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
namespace Kateglo\PusbaBundle\Model;


use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 */
class Entry {

    /**
     * @var string
     */
    private $entry;

    /**
     * @var string
     */
    private $syllable;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $discipline;

    /**
     * @var ArrayCollection
     */
    private $definitions;

    /**
     * @var ArrayCollection
     */
    private $samples;

    /**
     * @var ArrayCollection
     */
    private $synonyms;

    public function __construct(){
        $this->definitions = new ArrayCollection();
        $this->samples = new ArrayCollection();
        $this->synonyms = new ArrayCollection();
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param ArrayCollection $definitions
     */
    public function setDefinitions(ArrayCollection $definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * @return ArrayCollection
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * @param string $discipline
     */
    public function setDiscipline($discipline)
    {
        $this->discipline = $discipline;
    }

    /**
     * @return string
     */
    public function getDiscipline()
    {
        return $this->discipline;
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
     * @param ArrayCollection $samples
     */
    public function setSamples(ArrayCollection $samples)
    {
        $this->samples = $samples;
    }

    /**
     * @return ArrayCollection
     */
    public function getSamples()
    {
        return $this->samples;
    }

    /**
     * @param $syllable
     */
    public function setSyllable($syllable)
    {
        $this->syllable = $syllable;
    }

    /**
     * @return string
     */
    public function getSyllable()
    {
        return $this->syllable;
    }

    /**
     * @param ArrayCollection $synonyms
     */
    public function setSynonyms(ArrayCollection $synonyms)
    {
        $this->synonyms = $synonyms;
    }

    /**
     * @return ArrayCollection
     */
    public function getSynonyms()
    {
        return $this->synonyms;
    }
}
