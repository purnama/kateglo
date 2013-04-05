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
    private $result;

    public function __construct(){
        $this->result = new ArrayCollection();
    }

    public function getResult(){
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

    protected function checkTag(\DOMNode $node, $tagName){
        if($node->nodeName !== $tagName){
            throw new \Exception('Expecting html tag.');
        }
    }

    protected function parseBody(\DOMNode $body)
    {
        $entry = array('entry' => $this->param);

        $firstChild = $body->firstChild;
        if ($firstChild->nodeName === 'b') {
            /** @var $b \DOMNode */
            foreach ($firstChild->childNodes as $b) {
                if ($b->nodeName === '#text') {
                    $entry['syllable'] = trim($b->nodeValue);
                }
            }
        } else {
            throw new \Exception('Syllable not Found');
        }

        $firstSibling = $firstChild->nextSibling->nextSibling;
        if ($firstSibling->nodeName === 'i') {
            $entry['class'] = trim($firstSibling->nodeValue);
        } else {
            throw new \Exception('Classification not Found. Node Name:' . $firstSibling->nodeName . ' Node Value:' . $firstSibling->nodeValue);
        }

        $secondSibling = $firstSibling->nextSibling;
        if ($secondSibling->nodeName === '#text') {
            $definitions = explode(';', $secondSibling->nodeValue);
            foreach ($definitions as $definition) {
                $definition = trim($definition);
                if ($definition !== '') {
                    $entry['definition'][] = $definition;
                }
            }
        } else {
            throw new \Exception('Definition not Found');
        }

        $result->add($entry);

        if ($secondSibling->nextSibling->nodeName !== 'br') {
            throw new \Exception('Instead br found Node:' . $secondSibling->nextSibling->nodeValue);
        }

        $this->parseRestDefinition($secondSibling->nextSibling->nextSibling, $result);
    }

    protected function parseRestDefinition(\DOMNode $node, ArrayCollection $result)
    {
        if ($node->nodeName === '#text') {
            if ($node->nodeValue === '--') {
                $entrySibling = $node->nextSibling;
                if ($entrySibling->nodeName === 'i') {
                    $entryRaw = explode(',', $entrySibling->nodeValue);
                    $entry['entry'] = $this->param . ' ' . trim($entryRaw[0]);
                    $entry['class'] = trim($entryRaw[1]);
                    $definitionSibling = $entrySibling->nextSibling;
                    if ($definitionSibling->nodeName === '#text') {
                        $definitionRaw = explode(';', $definitionSibling->nodeValue);
                        $entry['definition'] = trim($definitionRaw[0]);
                        $result->add($entry);
                    } else {
                        throw new \Exception('Definition Sibling not found. Found:' . $definitionSibling->nodeName);
                    }
                } else {
                    throw new \Exception('Sibling ' . $entrySibling->nodeName . ' not expected');
                }
            }
        } elseif ($node->nodeName === 'b') {

        } else {
            throw new \Exception('Next Step not understand');
        }
    }
}
