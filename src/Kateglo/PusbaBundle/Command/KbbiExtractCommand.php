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
use Kateglo\PusbaBundle\Entity\KbbiDefinitionExtracted;
use Kateglo\PusbaBundle\Entity\KbbiEntryCrawl;
use Kateglo\PusbaBundle\Entity\KbbiEntryExtracted;
use Kateglo\PusbaBundle\Entity\KbbiSampleExtracted;
use Kateglo\PusbaBundle\Entity\KbbiSynonymExtracted;
use Kateglo\PusbaBundle\Model\Entry;
use Kateglo\PusbaBundle\Repository\EntryCrawlRepository;
use Kateglo\PusbaBundle\Repository\EntryListRepository;
use Kateglo\PusbaBundle\Service\Kbbi\Exception\KbbiParserException;
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
     * @var EntryListRepository
     */
    private $listRepository;

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
                    new InputOption('start', '-t', InputOption::VALUE_REQUIRED, 'Start id to extract in a transaction', 0),
                    new InputOption('limit', '-l', InputOption::VALUE_REQUIRED, 'Limit to extract in a transaction', 100),
                    new InputOption('verbose', '-v', InputOption::VALUE_NONE, 'Verbose'),
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
        $this->listRepository = $this->getContainer()->get('kateglo.pusba_bundle.repository.entry_list_repository');
        $this->parser = $this->getContainer()->get('kateglo.pusba_bundle.service.kbbi.parser');
        $this->progress = $this->getHelperSet()->get('progress');

        $limit = $input->getOption('limit');
        $start = $input->getOption('start');
        $verbose = $input->getOption('verbose');

        try {
            $output->writeln('<info>Start Extracting!</info>');
            $this->extract($start, $limit, $verbose, $output);
            $this->progress->finish();
            $output->writeln('<info>Extract successful!</info>');

        } catch (\Exception $e) {
            $output->writeln(
                sprintf(
                    'something is wrong. here is the error text:' . "\n" .
                    '<error>%s: %s</error>',
                    get_class($e),
                    $e->getMessage()
                )
            );
            $output->writeln($e->getTraceAsString());
        }
    }

    /**
     * @param int $start
     * @param int $limit
     * @param boolean $verbose
     * @param OutputInterface $output
     * @throws \Exception
     */
    private function extract($start = 0, $limit = 100, $verbose = false, OutputInterface $output)
    {
        @ini_set('memory_limit', '3069M');

        $entries = $this->crawlRepository->findAll($start, $limit);
        try {
            /** @var $entryRaw KbbiEntryCrawl */
            $countEntries = count($entries);
            $this->progress->start($output, $countEntries);
            for ($i = 0; $i < $countEntries; $i++) {
                $entryRaw = $entries[$i];
                try {
                    $this->parser->parse($entryRaw->getRaw());
                    $entryList = $this->parser->getResult();
                    /** @var $entryExtracted Entry */
                    foreach ($entryList as $entryExtracted) {
                        $entry = new KbbiEntryExtracted();
                        $entry->setEntry($entryExtracted->getEntry());
                        $entry->setCrawl($entryRaw);
                        if (!is_null($entryExtracted->getClass())) {
                            $entry->setClass($entryExtracted->getClass());
                        }
                        if (!is_null($entryExtracted->getSyllable())) {
                            $entryExtracted->setSyllable($entryExtracted->getSyllable());
                        }
                        if (!is_null($entryExtracted->getDiscipline())) {
                            $entry->setDiscipline($entryExtracted->getDiscipline());
                        }
                        $this->crawlRepository->getEntityManager()->persist($entry);


                        if ($entryExtracted->getDefinitions()->count() > 0) {
                            foreach ($entryExtracted->getDefinitions() as $definitionString) {
                                $definition = new KbbiDefinitionExtracted();
                                $definition->setEntry($entry);
                                $definition->setDefinition($definitionString);
                                $this->crawlRepository->getEntityManager()->persist($definition);
                            }
                        }

                        if ($entryExtracted->getSamples()->count() > 0) {
                            foreach ($entryExtracted->getSamples() as $sampleString) {
                                $sample = new KbbiSampleExtracted();
                                $sample->setEntry($entry);
                                $sample->setSample($sampleString);
                                $this->crawlRepository->getEntityManager()->persist($sample);
                            }
                        }

                        if ($entryExtracted->getSynonyms()->count() > 0) {
                            foreach ($entryExtracted->getSynonyms() as $synonymString) {
                                $synonym = new KbbiSynonymExtracted();
                                $synonym->setEntry($entry);
                                $synonym->setSynonym($synonymString);
                                $this->crawlRepository->getEntityManager()->persist($synonym);
                            }
                        }

                        try {
                            $entryList = $this->listRepository->findByEntry($entryExtracted->getEntry());
                            $entryList->setExtracted(true);
                            $this->listRepository->persist($entryList);
                        } catch (NoResultException $e) {
                            continue;
                        }
                    }
                    $this->progress->advance();
                } catch (KbbiParserException $e) {
                    if ($verbose) {
                        $output->writeln('<error>'.$entryRaw->getEntry() . ' can not be parse!</error>');
                    }
                    $this->createFile($output, $verbose, 'extracted_' . $entryRaw->getEntry() . '.html', $entryRaw->getRaw());
                    throw $e;
                }
            }
            $this->crawlRepository->flush();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function createFile(OutputInterface $output, $verbose = false, $filename, $content)
    {
        $filename = $this->getContainer()->getParameter(
                'kateglo_pusba.kbbi.directory'
            ) . DIRECTORY_SEPARATOR . str_replace(' ', '_', $filename);
        if (file_put_contents($filename, $content) !== false) {
            if ($verbose) {
                $output->writeln('<info>File: ' . $filename . ' created.</info>');
            }
        } else {
            throw new \Exception('<error>File ' . $filename . ' can not be created.</error>');
        }
    }

}
