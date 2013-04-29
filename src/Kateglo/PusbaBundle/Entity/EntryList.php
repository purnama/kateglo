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
namespace Kateglo\PusbaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 * @Entity
 */
class EntryList
{

    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string", unique=true)
     */
    protected $entry;

    /**
     * @var bool
     * @Column(type="boolean")
     */
    protected $found = false;

    /**
     * @var bool
     * @Column(type="boolean")
     */
    protected $extracted = false;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Kateglo\PusbaBundle\Entity\EntryCrawl", mappedBy="list")
     */
    protected $crawls;

    /**
     * Constructor
     */
    public function __construct(){
        $this->crawls = new ArrayCollection();
    }

    /**
     * @param string $entry
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;
    }

    /**
     * @return string
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param bool $found
     */
    public function setFound($found)
    {
        $this->found = $found;
    }

    /**
     * @return bool
     */
    public function getFound()
    {
        return $this->found;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param ArrayCollection $crawls
     */
    public function setCrawls(ArrayCollection $crawls)
    {
        $this->crawls = $crawls;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCrawls()
    {
        return $this->crawls;
    }

    /**
     * @param boolean $extracted
     */
    public function setExtracted($extracted)
    {
        $this->extracted = $extracted;
    }

    /**
     * @return boolean
     */
    public function getExtracted()
    {
        return $this->extracted;
    }

}
