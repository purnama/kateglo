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
use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * @author  Arthur Purnama <arthur@purnama.de>
 * @Service
 */
class Parser
{
    /**
     * @var ArrayCollection
     */
    private $result;

    public function __construct()
    {
        $this->result = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getResult()
    {
        return $this->result;
    }

    public function parse($content)
    {
        //Create Dom Document
        $dom = new \DOMDocument('', 'UTF-8');

        //KBBI HTML is invalid. Add an i end tag before load.
        $dom->loadHTML($content . '</i>');

        /** @var $html \DOMNode */
        foreach ($dom->childNodes as $html) {
            $this->checkTag($html, 'html');
            if ($html->hasChildNodes()) {
                /** @var $body \DOMNode */
                foreach ($html->childNodes as $body) {
                    $this->checkTag($body, 'body');
                    $this->parseBody($body);
                }
            }
        }
    }

    protected function checkTag(\DOMNode $node, $tagName)
    {
        if ($node->nodeName !== $tagName) {
            throw new \Exception('Expecting ' . $tagName . ' tag.');
        }
    }

    protected function parseBody(\DOMNode $body)
    {

        $firstChild = $body->firstChild;
        $this->checkTag($firstChild, 'b');
        /** @var $b \DOMNode */
        foreach ($firstChild->childNodes as $b) {
            if ($b->nodeName === '#text') {
                $entry['syllable'] = trim($b->nodeValue);
                $entry['entry'] = str_ireplace('·', '', $entry['syllable']);
            }
        }

        $firstSibling = $firstChild->nextSibling->nextSibling;
        $this->checkTag($firstSibling, 'i');
        $entry['class'] = trim($firstSibling->nodeValue);

        $secondSibling = $firstSibling->nextSibling;
        $this->checkTag($secondSibling, '#text');
        $definitions = explode(';', $secondSibling->nodeValue);
        foreach ($definitions as $definition) {
            $definition = trim($definition);
            if ($definition !== '') {
                $entry['definition'][] = $definition;
            }
        }


        $this->result->add($entry);
        $this->checkTag($secondSibling->nextSibling, 'br');


        $this->parseRestDefinition($secondSibling->nextSibling);
    }

    protected function parseRestDefinition(\DOMNode $node)
    {
        if ($node->nextSibling instanceof \DOMNode) {
            do {
                $node = $node->nextSibling;
                if ($node->nodeName === '#text') {
                    if ($node->nodeValue === '--') {
                        $this->parseInheritance($node);
                    }
                } elseif ($node->nodeName === 'b') {

                } else {
                    throw new \Exception('Next Step not understand');
                }
            } while ($node->nextSibling instanceof \DOMNode);
        } else {
            throw new \Exception('Next Sibling not found.');
        }
    }

    protected function parseInheritance(\DOMNode $node)
    {
        $entrySibling = $node->nextSibling;
        if ($entrySibling->nodeName === 'i') {
            $entryRaw = explode(',', $entrySibling->nodeValue);
            if (strpos(trim($entryRaw[0]), '--') === false) {
                $entry['entry'] = $this->result->get(0)['entry'] . ' ' . trim($entryRaw[0]);
            } else {
                $entry['entry'] = str_replace('--', $this->result->get(0)['entry'], trim($entryRaw[0]));
            }
            $entry['class'] = trim($entryRaw[1]);
            $definitionSibling = $entrySibling->nextSibling;
            if ($definitionSibling->nodeName === '#text') {
                $definitionRaw = explode(';', $definitionSibling->nodeValue);
                $entry['definition'] = trim($definitionRaw[0]);
                $this->result->add($entry);
                if (count($definitionRaw) > 1 && (trim($definitionRaw[1]) === '--' || trim($definitionRaw[1]) === '')) {
                    $this->parseInheritance($definitionSibling);
                }
            } else {
                throw new \Exception('Definition Sibling not found. Found:' . $definitionSibling->nodeName);
            }
        } else {
            throw new \Exception('Sibling ' . $entrySibling->nodeName . ' not expected');
        }
    }
}
