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

use Kateglo\PusbaBundle\Service\Kbbi;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 */
class KbbiTestCommand extends ContainerAwareCommand
{

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('pusba:kbbi:test')
            ->setDescription('Test Pengambilan data dari KBBI')
            ->setDefinition(
                array(
                    new InputArgument('entry', InputArgument::REQUIRED, 'Entri Pencarian'),
                    new InputOption('opcode', '-o', InputOption::VALUE_REQUIRED, 'Aturan Pencarian 1:Sama dengan, 2:diawali, 3:memuat', 1),
                    new InputOption('create', '-c', InputOption::VALUE_REQUIRED, 'Create Raw File [search, definition or extracted]'),
                    new InputOption('verbose', '-v', InputOption::VALUE_NONE, 'Verbose'),
                )
            )
            ->setHelp(
                <<<EOT
                Perintah <info>pusba:kbbi:test</info> mencoba mengambil dan membaca data dari web KBBI.

Contoh penggunaan:

<info>php app/console pusba:kbbi:test -v kapal</info>

Aturan pencarian bisa digunakan dengan menambahkan parameter --opkode atau -o.
Contoh:

<info>php app/console pusba:kbbi:test --entri=kapal -o=2</info>

Tanpa menambahkah aturan pencarian, maka otomatis aturan pencarian adalah 1:Sama dengan.
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
        $entry = $input->getArgument('entry');
        $opcode = $input->getOption('opcode');
        $create = $input->getOption('create');
        $verbose = $input->getOption('verbose');

        /** @var $kbbiService Kbbi */
        $kbbiService = $this->getContainer()->get('kateglo.pusba_bundle.service.kbbi.requester');

        $kbbiService->setOpCode($opcode);
        $kbbiService->setParam($entry);
        try {
            switch ($create) {
                case 'search':
                    $this->createFile($output, $verbose, 'search_' . $entry . '.html', $kbbiService->getRaw());
                    break;
                case 'definition' :
                    $wordList = $kbbiService->getRawDefinition();
                    foreach ($wordList as $word => $content) {
                        $this->createFile($output, $verbose, 'definition_' . $word . '.html', $content);
                    }
                    break;
                case 'extracted' :
                    $wordList = $kbbiService->getRawExtracted();
                    foreach ($wordList as $word => $content) {
                        $this->createFile($output, $verbose, 'extracted_' . $word . '.html', $content);
                    }
                    break;
                case null:
                    $result = $kbbiService->request();
                    if ($verbose) {
                        $output->writeln(sprintf('<info>%s</info>', $result));
                    }
                    break;
                default:
                    throw new \Exception('Create option not found');
            }
        } catch (Kbbi\Exception\KbbiRequestStatusException $e) {
            $output->writeln('<error>Request has an error: '.$e->getMessage().'</error>');
        }catch (Kbbi\Exception\KbbiExtractorException $e) {
            $output->writeln('<error>Extracting has an error: '.$e->getMessage().'</error>');
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
