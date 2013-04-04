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
/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 * @Service
 */
class Curl
{

    /**
     * @var bool
     */
    protected $followLocation;

    /**
     * @var int
     */
    protected $timeout;

    /**
     * @var int
     */
    protected $maxRedirects;

    /**
     * @var bool
     */
    protected $binaryTransfer;

    /**
     * @var bool
     */
    protected $includeHeader;

    /**
     * @var bool
     */
    protected $noBody;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $cookieFileLocation;

    /**
     * @var bool
     */
    protected $post;

    /**
     * @var array
     */
    protected $postFields;

    /**
     * @var string
     */
    protected $referer;

    /**
     * @var string
     */
    protected $result;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var bool
     */
    protected $authentication;

    /**
     * @var string
     */
    protected $authName;

    /**
     * @var string
     */
    protected $authPass;

    /**
     * @param bool $followLocation
     * @param int $timeOut
     * @param int $maxRedirects
     * @param bool $binaryTransfer
     * @param bool $includeHeader
     * @param bool $noBody
     * @param string $userAgent
     */
    public function __construct(
        $followLocation = true,
        $timeOut = 30,
        $maxRedirects = 4,
        $binaryTransfer = false,
        $includeHeader = false,
        $noBody = false,
        $userAgent = 'Mozilla/5.0 (Windows NT 6.0; rv:19.0)'
    ) {
        $this->followLocation = $followLocation;
        $this->timeout = $timeOut;
        $this->maxRedirects = $maxRedirects;
        $this->noBody = $noBody;
        $this->includeHeader = $includeHeader;
        $this->binaryTransfer = $binaryTransfer;
        $this->userAgent = $userAgent;
    }

    /**
     * @param string $authName
     */
    public function setAuthName($authName)
    {
        $this->authName = $authName;
    }

    /**
     * @return string
     */
    public function getAuthName()
    {
        return $this->authName;
    }

    /**
     * @param string $authPass
     */
    public function setAuthPass($authPass)
    {
        $this->authPass = $authPass;
    }

    /**
     * @return string
     */
    public function getAuthPass()
    {
        return $this->authPass;
    }

    /**
     * @param boolean $authentication
     */
    public function setAuthentication($authentication)
    {
        $this->authentication = $authentication;
    }

    /**
     * @return boolean
     */
    public function getAuthentication()
    {
        return $this->authentication;
    }

    /**
     * @param string $cookieFileLocation
     */
    public function setCookieFileLocation($cookieFileLocation)
    {
        $this->cookieFileLocation = $cookieFileLocation;
    }

    /**
     * @return string
     */
    public function getCookieFileLocation()
    {
        return $this->cookieFileLocation;
    }

    /**
     * @param boolean $post
     */
    public function setPost($post)
    {
        $this->post = $post;
    }

    /**
     * @return boolean
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param array $postFields
     */
    public function setPostFields($postFields)
    {
        $this->postFields = $postFields;
    }

    /**
     * @return array
     */
    public function getPostFields()
    {
        return $this->postFields;
    }

    /**
     * @param string $referer
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;
    }

    /**
     * @return string
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * @param string $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param string $url
     */
    public function setUrl($url){
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return boolean
     */
    public function getFollowLocation()
    {
        return $this->followLocation;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @return int
     */
    public function getMaxRedirects()
    {
        return $this->maxRedirects;
    }

    /**
     * @return boolean
     */
    public function getBinaryTransfer()
    {
        return $this->binaryTransfer;
    }

    /**
     * @return boolean
     */
    public function getIncludeHeader()
    {
        return $this->includeHeader;
    }

    /**
     * @return boolean
     */
    public function getNoBody()
    {
        return $this->noBody;
    }

    public function execute()
    {
        if (!is_string($this->url) || $this->url === '') {
            throw new \HttpUrlException;
        }

        $result = curl_init();

        curl_setopt($result, CURLOPT_URL, $this->url);
        curl_setopt($result, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($result, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($result, CURLOPT_MAXREDIRS, $this->maxRedirects);
        curl_setopt($result, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($result, CURLOPT_FOLLOWLOCATION, $this->followLocation);

        curl_setopt($result, CURLOPT_PROXY, 'wwwproxy.bahn-net.db.de:8080');
        curl_setopt($result, CURLOPT_PROXYUSERPWD, 'arthurpurnama:s3k0l4H80!siang');

        if (is_string($this->cookieFileLocation) && $this->cookieFileLocation !== '') {
            curl_setopt($result, CURLOPT_COOKIEJAR, $this->cookieFileLocation);
            curl_setopt($result, CURLOPT_COOKIEFILE, $this->cookieFileLocation);
        }

        if ($this->authentication === true) {
            curl_setopt($result, CURLOPT_USERPWD, $this->authName . ':' . $this->authPass);
        }
        if ($this->post === true) {
            curl_setopt($result, CURLOPT_POST, true);
            curl_setopt($result, CURLOPT_POSTFIELDS, $this->postFields);

        }

        if ($this->includeHeader === true) {
            curl_setopt($result, CURLOPT_HEADER, true);
        }

        if ($this->noBody === true) {
            curl_setopt($result, CURLOPT_NOBODY, true);
        }

        curl_setopt($result, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($result, CURLOPT_REFERER, $this->referer);

        $this->result = curl_exec($result);
        $this->status = curl_getinfo($result, CURLINFO_HTTP_CODE);
        curl_close($result);
    }
}
