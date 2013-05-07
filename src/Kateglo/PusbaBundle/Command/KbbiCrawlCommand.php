<?php
/**
 *  Kateglo: Kamus, Tesaurus dan Glosarium bahasa Indonesia.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the GPL 2.0. For more information, see
 * <http://code.google.com/p/kateglo/>.
 *
 * @license <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html> GPL 2.0
 * @link    http://code.google.com/p/kateglo/
 * @copyright Copyright (c) 2009 Kateglo (http://code.google.com/p/kateglo/)
 */
namespace Kateglo\PusbaBundle\Command;


use Doctrine\ORM\NoResultException;
use Kateglo\PusbaBundle\Entity\EntryCrawl;
use Kateglo\PusbaBundle\Entity\EntryCrawlConfig;
use Kateglo\PusbaBundle\Entity\EntryCrawlHistory;
use Kateglo\PusbaBundle\Entity\EntryList;
use Kateglo\PusbaBundle\Repository\EntryCrawlRepository;
use Kateglo\PusbaBundle\Repository\EntryListRepository;
use Kateglo\PusbaBundle\Service\Kbbi\Exception\KbbiExtractorException;
use Kateglo\PusbaBundle\Service\Kbbi\Requester;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 */
class KbbiCrawlCommand extends ContainerAwareCommand
{
    const STATUS_CRAWLING = "crawling";

    const STATUS_FINISH = "finish";

    /**
     * @var EntryListRepository
     */
    private $listRepository;

    /**
     * @var EntryCrawlRepository
     */
    private $crawlRepository;

    /**
     * @var Requester
     */
    private $requester;

    /**
     * @var ProgressHelper
     */
    private $progress;

    /**
     * @see Command
     */
    protected function configure()
    {

        $this
            ->setName('pusba:kbbi:crawl')
            ->setDescription('Crawl Kbbi Online from the Database List')
            ->setDefinition(
                array(
                    new InputOption('limit', '-l', InputOption::VALUE_REQUIRED, 'Limit to crawl', 100),
                    new InputOption('start', '-t', InputOption::VALUE_NONE, 'Start over the crawling process'),
                )
            )
            ->setHelp(
                <<<EOT
                Perintah <info>pusba:kbbi:crawl</info> meng crawl data dari Kbbi Online ke database.
Data Crawl menggunakan daftar yang ada di database

Contoh penggunaan:

<info>php app/console pusba:kbbi:crawl </info>

Struktur CSV file harus sedemikian rupa:

hutang
huyung
ia
ialah

dimana baris baru harus berupa '\n'
EOT
            );
    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When namespace doesn't end with Bundle
     * @throws \RuntimeException         When bundle can't be executed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->listRepository = $this->getContainer()->get('kateglo.pusba_bundle.repository.entry_list_repository');
        $this->crawlRepository = $this->getContainer()->get('kateglo.pusba_bundle.repository.entry_crawl_repository');
        $this->requester = $this->getContainer()->get('kateglo.pusba_bundle.service.kbbi.requester');
        $this->progress = $this->getHelperSet()->get('progress');

        $limit = $input->getOption('limit');
        $start = $input->getOption('start');

        try {
            if ($this->getStatus() === self::STATUS_FINISH) {
                $this->setStatus(self::STATUS_CRAWLING);
                $output->writeln('<info>Start Crawling!</info>');
                $this->progress->start($output, $limit);
                $this->crawl($limit, $start);
                $this->setStatus(self::STATUS_FINISH);
                $this->progress->finish();
                $output->writeln('<info>Crawl successful!</info>');
            } else {
                $output->writeln('<info>Crawl still in progress! Try again later</info>');
            }
        } catch (\Exception $e) {
            $this->setStatus(self::STATUS_FINISH);
            $output->writeln(
                sprintf(
                    'something is wrong. here is the error text:' . "\n" .
                        '<error>%s</error>',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @param int $limit
     * @param bool $start
     * @throws \Exception
     */
    private function crawl($limit = 100, $start = false)
    {
        @ini_set('memory_limit', '3069M');
        try {
            $config = $this->crawlRepository->getCrawlConfig();
        } catch (NoResultException $e) {
            $config = new EntryCrawlConfig();
        }
        if ($start) {
            $config->setLastId(0);
            $this->listRepository->reset();
        }
        $entries = $this->listRepository->findAll($config->getLastId(), $limit);
        $this->requester->setOpCode(1);
        $crawlHistory = new EntryCrawlHistory();
        $crawlHistory->setStartId($config->getLastId());
        $crawlHistory->setStartTime(new \DateTime());
        try {
            /** @var $entry EntryList */
            $countEntries = count($entries);
            for ($i = 0; $i < $countEntries; $i++) {
                $entry = $entries[$i];
                $this->requester->setParam($entry->getEntry());
                try {
                    $wordList = $this->requester->getRawExtracted();
                    foreach ($wordList as $word => $content) {
                        try {
                            $entryCrawl = $this->crawlRepository->findByEntry($word);
                        } catch (NoResultException $e) {
                            $entryCrawl = new EntryCrawl();
                        }
                        $entryCrawl->setEntry($word);
                        $entryCrawl->setRaw($content);
                        $entryCrawl->setList($entry);
                        $entryCrawl->setLastUpdated(new \DateTime());
                        $this->crawlRepository->persist($entryCrawl);
                    }
                    $entry->setFound(true);
                    $this->listRepository->persist($entry);
                    $this->progress->advance();
                } catch (KbbiExtractorException $e) {
                    $this->progress->advance();
                    continue;
                }
            }
            $config->setLastId($entry->getId());
            $config->setLastUpdated(new \DateTime());
            $this->crawlRepository->persistConfig($config);
            $crawlHistory->setFinishId($entry->getId());
            $crawlHistory->setFinishTime(new \DateTime());
            $crawlHistory->setStatus(true);
            $this->crawlRepository->persistHistory($crawlHistory);
            $this->crawlRepository->flush();
        } catch (\Exception $e) {
            $crawlHistory->setFinishTime(new \DateTime());
            $crawlHistory->setStatus(false);
            $crawlHistory->setMessages($e->getMessage());
            $this->crawlRepository->persistHistory($crawlHistory);
            $this->crawlRepository->flush();
            throw $e;
        }
    }

    /**
     * @return string
     */
    private function getStatus(){
        $config = $this->crawlRepository->getCrawlConfig();
        return $config->getStatus();
    }

    /**
     * @param string $status
     */
    private function setStatus($status){
        $config = $this->crawlRepository->getCrawlConfig();
        $config->setStatus($status);
        $this->crawlRepository->persistConfig($config);
        $this->crawlRepository->flush();
    }
}
