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
namespace Kateglo\PusbaBundle\Tests\Service\Kbbi;


use Kateglo\PusbaBundle\Service\Kbbi\Parser;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Parser;
     */
    private $testObj;

    private $resourceDir;

    public function setUp()
    {
        $this->testObj = new Parser();
        $this->resourceDir = dirname(
            dirname(dirname(__FILE__))
        ) . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'Kbbi' . DIRECTORY_SEPARATOR;
    }

    /**
     *
     * @test
     */
    public function parseAir(){
        $result = $this->getParseResult('extracted_air.html');
        $this->assertEquals(163, $result->count());
    }

    /**
     *
     * @test
     */
    public function parseAlur(){
        $result = $this->getParseResult('extracted_alur.html');
        $this->assertEquals(31, $result->count());
    }

    /**
     *
     * @test
     */
    public function parseBeriTahu(){
        $result = $this->getParseResult('extracted_beri_tahu.html');
        $this->assertEquals(6, $result->count());
    }

    /**
     *
     * @test
     */
    public function parseHarta(){
        $result = $this->getParseResult('extracted_harta.html');
        $this->assertEquals(40, $result->count());
    }

    /**
     *
     * @test
     */
    public function parseHutan(){
        $result = $this->getParseResult('extracted_hutan.html');
        $this->assertEquals(40, $result->count());
    }

    /**
     *
     * @test
     */
    public function parseKapal1()
    {
        $result = $this->getParseResult('extracted_kapal_(1).html');
        $this->assertEquals(61, $result->count());
    }

    /**
     *
     * @test
     */
    public function parseKapal2()
    {
        $result = $this->getParseResult('extracted_kapal_(2).html');
        $this->assertEquals(2, $result->count());
    }

    private function getParseResult($filename)
    {
        $content = file_get_contents($this->resourceDir . $filename);
        $this->testObj->parse($content);
        return $this->testObj->getResult();
    }
}
