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
class Input
{

    /**
     * @var string
     * @XmlAttribute
     */
    private $type;

    /**
     * @var string
     * @XmlAttribute
     */
    private $value;

    /**
     * @var string
     * @XmlAttribute
     */
    private $name;

    /**
     * @var string
     * @XmlAttribute
     */
    private $alt;

    /**
     * @var string
     * @XmlAttribute
     */
    private $maxlength;

    /**
     * @var string
     * @XmlAttribute
     */
    private $checked;

    /**
     * @var string
     * @XmlAttribute
     */
    private $disabled;

    /**
     * @var string
     * @XmlAttribute
     */
    private $readonly;

    /**
     * @var string
     * @XmlAttribute
     */
    private $size;

    /**
     * @var string
     * @XmlAttribute
     */
    private $src;

    /**
     * @var string
     * @XmlAttribute
     */
    private $accept;

    function __construct(
        $type = null,
        $value = null,
        $name = null,
        $alt = null,
        $maxlength = null,
        $checked = null,
        $disabled = null,
        $readonly = null,
        $size = null,
        $src = null,
        $accept = null
    ) {
        $this->accept = $accept;
        $this->alt = $alt;
        $this->checked = $checked;
        $this->disabled = $disabled;
        $this->maxlength = $maxlength;
        $this->name = $name;
        $this->readonly = $readonly;
        $this->size = $size;
        $this->src = $src;
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * @param string $accept
     */
    public
    function setAccept(
        $accept
    ) {
        $this->accept = $accept;
    }

    /**
     * @return string
     */
    public
    function getAccept()
    {
        return $this->accept;
    }

    /**
     * @param string $alt
     */
    public
    function setAlt(
        $alt
    ) {
        $this->alt = $alt;
    }

    /**
     * @return string
     */
    public
    function getAlt()
    {
        return $this->alt;
    }

    /**
     * @param string $checked
     */
    public
    function setChecked(
        $checked
    ) {
        $this->checked = $checked;
    }

    /**
     * @return string
     */
    public
    function getChecked()
    {
        return $this->checked;
    }

    /**
     * @param string $disabled
     */
    public
    function setDisabled(
        $disabled
    ) {
        $this->disabled = $disabled;
    }

    /**
     * @return string
     */
    public
    function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param string $maxlength
     */
    public
    function setMaxlength(
        $maxlength
    ) {
        $this->maxlength = $maxlength;
    }

    /**
     * @return string
     */
    public
    function getMaxlength()
    {
        return $this->maxlength;
    }

    /**
     * @param string $name
     */
    public
    function setName(
        $name
    ) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public
    function getName()
    {
        return $this->name;
    }

    /**
     * @param string $readonly
     */
    public
    function setReadonly(
        $readonly
    ) {
        $this->readonly = $readonly;
    }

    /**
     * @return string
     */
    public
    function getReadonly()
    {
        return $this->readonly;
    }

    /**
     * @param string $size
     */
    public
    function setSize(
        $size
    ) {
        $this->size = $size;
    }

    /**
     * @return string
     */
    public
    function getSize()
    {
        return $this->size;
    }

    /**
     * @param string $src
     */
    public
    function setSrc(
        $src
    ) {
        $this->src = $src;
    }

    /**
     * @return string
     */
    public
    function getSrc()
    {
        return $this->src;
    }

    /**
     * @param string $type
     */
    public
    function setType(
        $type
    ) {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public
    function getType()
    {
        return $this->type;
    }

    /**
     * @param string $value
     */
    public
    function setValue(
        $value
    ) {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public
    function getValue()
    {
        return $this->value;
    }
}
