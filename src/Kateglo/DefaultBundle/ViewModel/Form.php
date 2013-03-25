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

use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlAttribute;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 */
class Form
{

    /**
     * @var string
     * @XmlAttribute
     */
    private $action;

    /**
     * @var string
     * @XmlAttribute
     */
    private $method;

    /**
     * @var string
     * @XmlAttribute
     */
    private $name;

    /**
     * @var string
     * @XmlAttribute
     */
    private $enctype;

    /**
     * @var string
     * @XmlAttribute
     */
    private $target;

    /**
     * @var string
     * @XmlAttribute
     */
    private $accept;

    /**
     * @var string
     * @XmlAttribute
     */
    private $acceptCharset;

    /**
     * @var array
     * @XmlList(inline = true, entry = "input")
     */
    private $input;

    /**
     * @param array $input
     * @param string $action
     * @param string $method
     * @param string $name
     * @param string $enctype
     * @param string $target
     * @param string $accept
     * @param string $acceptCharset
     */
    function __construct(
        $input = array(),
        $action = null,
        $method = null,
        $name = null,
        $enctype = null,
        $target = null,
        $accept = null,
        $acceptCharset = null
    ) {
        $this->accept = $accept;
        $this->acceptCharset = $acceptCharset;
        $this->action = $action;
        $this->enctype = $enctype;
        $this->input = $input;
        $this->method = $method;
        $this->name = $name;
        $this->target = $target;
    }

    /**
     * @param string $accept
     */
    public function setAccept($accept)
    {
        $this->accept = $accept;
    }

    /**
     * @return string
     */
    public function getAccept()
    {
        return $this->accept;
    }

    /**
     * @param string $acceptCharset
     */
    public function setAcceptCharset($acceptCharset)
    {
        $this->acceptCharset = $acceptCharset;
    }

    /**
     * @return string
     */
    public function getAcceptCharset()
    {
        return $this->acceptCharset;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $enctype
     */
    public function setEnctype($enctype)
    {
        $this->enctype = $enctype;
    }

    /**
     * @return string
     */
    public function getEnctype()
    {
        return $this->enctype;
    }

    /**
     * @param array $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }

    /**
     * @return array
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }
}
