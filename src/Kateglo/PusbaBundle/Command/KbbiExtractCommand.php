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
use Kateglo\PusbaBundle\Entity\KbbiEntryCrawl;
use Kateglo\PusbaBundle\Model\Entry;
use Kateglo\PusbaBundle\Repository\EntryCrawlRepository;
use Kateglo\PusbaBundle\Service\Kbbi\Exception\KbbiExtractorException;
use Kateglo\PusbaBundle\Service\Kbbi\Parser;
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
class KbbiExtractCommand extends ContainerAwareCommand
{

    /**
     * @var EntryCrawlRepository
     */
    private $crawlRepository;

    /**
     * @var Parser
     */
    private $parser;

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
            ->setName('pusba:kbbi:extract')
            ->setDescription('Extract crawled Kbbi Online from the Database')
            ->setDefinition(
                array(
                    new InputOption('limit', '-l', InputOption::VALUE_REQUIRED, 'Limit to extract in a transaction', 100),
                )
            )
            ->setHelp(
                <<<EOT
                Perintah <info>pusba:kbbi:extract</info> meng extrak data dari Kbbi yang sudah di crawl ke database.
Data extract menggunakan daftar crawl yang ada di database

Contoh penggunaan:

<info>php app/console pusba:kbbi:extract </info>

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

        $this->crawlRepository = $this->getContainer()->get('kateglo.pusba_bundle.repository.entry_crawl_repository');
        $this->parser = $this->getContainer()->get('kateglo.pusba_bundle.service.parser');
        $this->progress = $this->getHelperSet()->get('progress');

        $limit = $input->getOption('limit');

        try {
                $output->writeln('<info>Start Extracting!</info>');
                $this->extract($limit);
                $this->progress->finish();
                $output->writeln('<info>Extract successful!</info>');

        } catch (\Exception $e) {
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
     * @param OutputInterface $output
     * @throws \Exception
     */
    private function extract($limit = 100, OutputInterface $output)
    {
        @ini_set('memory_limit', '3069M');

        $entries = $this->crawlRepository->findAll();
        try {
            /** @var $entry KbbiEntryCrawl */
            $countEntries = count($entries);
            $this->progress->start($output, $countEntries);
            for ($i = 0; $i < $countEntries; $i+$limit) {
                $entry = $entries[$i];
                try {
                    $wordList = $this->parser->parse($entry->getRaw());
                    /** @var $word Entry */
                    foreach ($wordList as $word) {
                        try {
                            $entryCrawl = $this->crawlRepository->findByEntry($word);
                        } catch (NoResultException $e) {
                            $entryCrawl = new KbbiEntryCrawl();
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

}
