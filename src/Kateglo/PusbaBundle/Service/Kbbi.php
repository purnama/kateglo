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
namespace Kateglo\PusbaBundle\Service;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 * @Service
 */
class Kbbi
{
    const SEARCH = 'OPKODE=%1$d&PARAM=%2$s&PERINTAH=Cari&PERINTAH2=&KATA=&DFTKATA=&MORE=0&HEAD=0';

    const DEFINE = 'DFTKATA=%1$s&HEAD=0&KATA=%2$s&MORE=0&OPKODE=%3$d&PARAM=%4$s&PERINTAH2=Tampilkan';

    /**
     * @var string url to kbbi
     */
    private $url;

    /**
     * @var Curl
     */
    private $curl;

    private $param;

    private $opCode;

    /**
     * @param string $url
     * @InjectParams({
     *  "url" = @Inject("%kateglo_pusba.kbbi.url%"),
     *  "curl" = @Inject("kateglo.pusba_bundle.service.curl")
     * })
     */
    public function __construct($url, Curl $curl)
    {
        $this->url = $url;
        $this->curl = $curl;
        $this->curl->setUrl($this->url);
        $this->curl->setPost(true);
    }

    public function request($param, $opCode)
    {
        $this->param = $param;
        $this->opCode = $opCode;
        $this->curl->setPostFields(sprintf(static::SEARCH, $opCode, $param));
        $this->curl->execute();
        if ($this->curl->getStatus() === 200) {
            return $this->parseDft($this->curl->getResult());
        } else {
            throw new \Exception('Status: ' . $this->curl->getStatus());
        }
    }

    protected function parseDft($result)
    {
        $pattern = '/<input type="hidden" name="DFTKATA" value="(.+)" >.+' .
            '<input type="hidden" name="MORE" value="(.+)" >.+' .
            '<input type="hidden" name="HEAD" value="(.+)" >/s';
        preg_match($pattern, $result, $match);
        if (is_array($match)) {
            if (is_numeric($match[2]) && $match[2] == 1) {
                throw new \HttpException('Match Paginated!');
            }
            $dft = $match[1];
            $entries = explode(';', $dft);
            $result = '';
            foreach ($entries as $entry) {
                $result .= $this->define($dft, $entry);
            }

            return $result;
        } else {
            throw new \Exception('Pattern can not match the result!');
        }
    }

    protected function define($dft, $entry)
    {
        $this->curl->setPostFields(sprintf(static::DEFINE, $dft, $entry, $this->opCode, $this->param));
        $this->curl->execute();
        if ($this->curl->getStatus() === 200) {
            $result = $this->curl->getResult();
            $pattern = '/(<p style=\'margin-left:\.5in;text-indent:-\.5in\'>)(.+)(<\/(p|BODY)>)/s';
            preg_match($pattern, $result, $match);

            if (is_array($match)) {
                $dom = new \DOMDocument();
                $dom->loadHTML($match[2] . '</i>');

                return $this->parseDefine($dom);
            } else {
                throw new \Exception('Pattern can not match the result!');
            }
        } else {
            throw new \Exception('Status: ' . $this->curl->getStatus());
        }
    }

    protected function parseDefine(\DOMDocument $dom)
    {
        $text = '';
        /** @var $html \DOMNode */
        foreach ($dom->childNodes as $html) {
            if ($html->hasChildNodes()) {
                /** @var $body \DOMNode */
                foreach ($html->childNodes as $body) {
                    print_r($body->firstChild->nodeName);
//                    foreach ($body->childNodes as $node) {
//                        print_r($node->nodeName."\n");
//                    }
                }
            }
        }

        return $text;
    }
}
