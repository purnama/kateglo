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

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 * @Entity
 */
class KbbiEntryExtracted
{

    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var KbbiEntryCrawl
     * @ManyToOne(targetEntity="Kateglo\PusbaBundle\Entity\KbbiEntryCrawl", inversedBy="extracts")
     */
    private $crawl;

    /**
     * @var string
     * @Column(type="string")
     */
    private $entry;

    /**
     * @var string
     * @Column(type="string")
     */
    private $syllable;

    /**
     * @var string
     * @Column(type="string")
     */
    private $class;

    /**
     * @var string
     * @Column(type="string")
     */
    private $discipline;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Kateglo\PusbaBundle\Entity\KbbiDefinitionExtracted", mappedBy="entry")
     */
    private $definitions;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Kateglo\PusbaBundle\Entity\KbbiSampleExtracted", mappedBy="entry")
     */
    private $samples;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Kateglo\PusbaBundle\Entity\KbbiSynonymExtracted", mappedBy="entry")
     */
    private $synonyms;

    public function __construct(){
        $this->definitions = new ArrayCollection();
        $this->samples = new ArrayCollection();
        $this->synonyms = new ArrayCollection();
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime $lastUpdated
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * @param string $raw
     */
    public function setRaw($raw)
    {
        $this->raw = $raw;
    }

    /**
     * @return string
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * @param \Kateglo\PusbaBundle\Entity\PusbaEntryList $list
     */
    public function setList(PusbaEntryList $list)
    {
        $this->list = $list;
    }

    /**
     * @return \Kateglo\PusbaBundle\Entity\PusbaEntryList
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param \Kateglo\PusbaBundle\Entity\KbbiEntryCrawl $crawl
     */
    public function setCrawl($crawl)
    {
        $this->crawl = $crawl;
    }

    /**
     * @return \Kateglo\PusbaBundle\Entity\KbbiEntryCrawl
     */
    public function getCrawl()
    {
        return $this->crawl;
    }

}
