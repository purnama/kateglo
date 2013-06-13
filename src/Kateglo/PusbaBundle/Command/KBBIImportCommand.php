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


use Kateglo\PusbaBundle\Service\ImportEntryList;
use Kateglo\PusbaBundle\Service\Kbbi\Importer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 */
class KbbiImportCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('pusba:kbbi:import')
            ->setDescription('Import Data dari CSV File')
            ->setDefinition(
                array(
                    new InputArgument('file', InputArgument::REQUIRED, 'File to import'),
                )
            )
            ->setHelp(
                <<<EOT
                Perintah <info>pusba:kbbi:import</info> mengimport data dari CSV ke database.

Contoh penggunaan:

<info>php app/console pusba:kbbi:import path/to/file </info>

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
        $file = $input->getArgument('file');

        /** @var $importService Importer */
        $importService = $this->getContainer()->get('kateglo.pusba_bundle.service.kbbi.importer');

        $content = file_get_contents($file);

        try {
            $importService->import($content);
            $output->writeln('<info>Import successful!</info>');
        } catch (\Exception $e) {
            $output->writeln(
                sprintf(
                    'something is wrong. here is the error text:' . "\r\n" .
                        '<error>%s</<error>',
                    $e->getMessage()
                )
            );
        }
    }
}
