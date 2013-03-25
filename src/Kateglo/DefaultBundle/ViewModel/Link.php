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
namespace Kateglo\DefaultBundle\ViewModel;

use JMS\Serializer\Annotation\XmlAttribute;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 */
class Link
{

    /**
     * @var string
     * @XmlAttribute
     */
    private $rel;

    /**
     * @var string
     * @XmlAttribute
     */
    private $title;

    /**
     * @var string
     * @XmlAttribute
     */
    private $href;

    /**
     * @var string
     * @XmlAttribute
     */
    private $hreflang;

    /**
     * @var string
     * @XmlAttribute
     */
    private $media;

    /**
     * @var string
     * @XmlAttribute
     */
    private $target;

    /**
     * @var string
     * @XmlAttribute
     */
    private $charset;

    function __construct(
        $href = null,
        $rel = null,
        $title = null,
        $hreflang = null,
        $charset = null,
        $media = null,
        $target = null
    ) {
        $this->charset = $charset;
        $this->href = $href;
        $this->hreflang = $hreflang;
        $this->media = $media;
        $this->rel = $rel;
        $this->target = $target;
        $this->title = $title;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    public function getCharset()
    {
        return $this->charset;
    }

    public function setHref($href)
    {
        $this->href = $href;
    }

    public function getHref()
    {
        return $this->href;
    }

    public function setHreflang($hreflang)
    {
        $this->hreflang = $hreflang;
    }

    public function getHreflang()
    {
        return $this->hreflang;
    }

    public function setMedia($media)
    {
        $this->media = $media;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function setRel($rel)
    {
        $this->rel = $rel;
    }

    public function getRel()
    {
        return $this->rel;
    }

    public function setTarget($target)
    {
        $this->target = $target;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
