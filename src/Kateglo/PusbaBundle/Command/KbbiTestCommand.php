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
                    new InputArgument('param', InputArgument::REQUIRED, 'Entri Pencarian'),
                    new InputOption('opcode', '-o', InputOption::VALUE_REQUIRED, 'Aturan Pencarian 1:Sama dengan, 2:diawali, 3:memuat', 1),
                )
            )
            ->setHelp(
                <<<EOT
                Perintah <info>kbbi:request:test</info> mencoba mengambil dan membaca data dari web KBBI.

Contoh penggunaan:

<info>php app/console kbbi:request:test --entri=kapal</info>

Aturan pencarian bisa digunakan dengan menambahkan parameter --opkode atau -o.
Contoh:

<info>php app/console generate:bundle --entri=kapal -o=2</info>

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
        $entry = $input->getArgument('param');
        $opcode = $input->getOption('opcode');

        /** @var $kbbiService Kbbi */
        $kbbiService = $this->getContainer()->get('kateglo.pusba_bundle.service.kbbi');

        $result = $kbbiService->request($entry, $opcode);
        $output->writeln(sprintf('Hasil: <comment>%s</comment>', $result));
    }

}
