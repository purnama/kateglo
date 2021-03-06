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
namespace Kateglo\PusbaBundle\Service\Kbbi;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Kateglo\PusbaBundle\Service\Kbbi\Exception\KbbiRequestStatusException;
use Kateglo\PusbaBundle\Service\Kbbi\Extractor;
use Kateglo\PusbaBundle\Service\Kbbi\Parser;
use Kateglo\PusbaBundle\Service\Curl;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 * @Service
 */
class Requester
{
    const SEARCH = 'OPKODE=%1$d&PARAM=%2$s&PERINTAH=Cari&PERINTAH2=&KATA=&DFTKATA=&MORE=0&HEAD=0';

    const DEFINITION = 'DFTKATA=%1$s&HEAD=0&KATA=%2$s&MORE=0&OPKODE=%3$d&PARAM=%4$s&PERINTAH2=Tampilkan';

    /**
     * @var string url to kbbi
     */
    private $url;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var Kbbi\Extractor
     */
    private $extractor;

    /**
     * @var Kbbi\Parser
     */
    private $parser;

    /**
     * @var string
     */
    private $param;

    /**
     * @var int
     */
    private $opCode;

    /**
     * @var string
     */
    private $listString;

    /**
     * @param $url
     * @param Curl $curl
     * @param Extractor $extractor
     * @param Parser $parser
     *
     * @InjectParams({
     *  "url" = @Inject("%kateglo_pusba.kbbi.url%"),
     *  "curl" = @Inject("kateglo.pusba_bundle.service.curl"),
     *  "extractor" = @Inject("kateglo.pusba_bundle.service.kbbi.extractor"),
     *  "parser" = @Inject("kateglo.pusba_bundle.service.kbbi.parser")
     * })
     */
    public function __construct($url, Curl $curl, Extractor $extractor, Parser $parser)
    {
        $this->url = $url;
        $this->curl = $curl;
        $this->extractor = $extractor;
        $this->parser = $parser;
        $this->curl->setUrl($this->url);
        $this->curl->setPost(true);
    }

    /**
     * @param string $param
     */
    public function setParam($param)
    {
        $this->param = $param;
    }

    /**
     * @param int $opCode
     */
    public function setOpCode($opCode)
    {
        $this->opCode = $opCode;
    }

    public function request()
    {
        $wordList = $this->getWordList();
        foreach ($wordList as $word) {
            $rawDefinition = $this->extractor->extractDefinition($this->requestDefinition($word));
            $this->parser->parse($rawDefinition);

            return json_encode($this->parser->getResult()->toArray());
        }

    }

    public function getRaw()
    {
        return $this->requestSearch();
    }

    public function getRawDefinition()
    {
        $wordList = $this->getWordList();
        $resultText = array();
        foreach ($wordList as $word) {
            $resultText[$word] = $this->requestDefinition($word);
        }

        return $resultText;
    }

    public function getRawExtracted()
    {
        $wordList = $this->getWordList();
        $resultText = array();
        foreach ($wordList as $word) {
            $resultText[$word] = $this->extractor->extractDefinition($this->requestDefinition($word));
        }

        return $resultText;
    }

    protected function getWordList()
    {
        //Extract the Result List
        $this->listString = $this->extractor->extractList($this->requestSearch());

        //For each words in the list get the Definition
        return explode(';', $this->listString);
    }

    protected function requestSearch()
    {
        if (empty($this->opCode) || empty($this->param)) {
            throw new \Exception('requestSearch(): Parameter can not be empty opcode:'.$this->opCode.' param:'.$this->param);
        }
        $this->curl->setPostFields(sprintf(static::SEARCH, $this->opCode, $this->param));
        $this->execute();

        return $this->curl->getResult();
    }

    protected function requestDefinition($word)
    {
        if (empty($this->listString) || empty($word) || empty($this->opCode) || empty($this->param)) {
            throw new \Exception('requestDefinition(): Parameter can not be empty');
        }
        $this->curl->setPostFields(sprintf(static::DEFINITION, $this->listString, $word, $this->opCode, $this->param));
        $this->execute();

        return $this->curl->getResult();
    }

    protected function execute()
    {
        $this->curl->execute();
        if ($this->curl->getStatus() !== 200) {
            throw new KbbiRequestStatusException('Status: ' . $this->curl->getStatus());
        }
    }

}
