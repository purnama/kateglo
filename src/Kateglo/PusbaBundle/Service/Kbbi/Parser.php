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
                    $this->parseBody($body->firstChild);
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

    protected function parseBody(\DOMNode $node)
    {
        $this->checkTag($node, 'b');
        /** @var $b \DOMNode */
        foreach ($node->childNodes as $b) {
            if ($b->nodeName === '#text') {
                $entry['syllable'] = trim($b->nodeValue);
                $entry['entry'] = str_ireplace('·', '', $entry['syllable']);
            }
        }

        $firstSibling = $node->nextSibling->nextSibling;
        $this->checkTag($firstSibling, 'i');
        $entry['class'] = trim($firstSibling->nodeValue);

        $secondSibling = $firstSibling->nextSibling;
        $this->checkTag($secondSibling, '#text');
        if (trim($secondSibling->nodeValue) === '') {
            $secondSibling = $secondSibling->nextSibling;
            if ($secondSibling->nodeName === 'b' && is_numeric($secondSibling->nodeValue)) {
                $secondSibling = $secondSibling->nextSibling;
                if ($secondSibling->nodeName !== '#text') {
                    throw new \Exception('Next Step not understand');
                }
            } else {
                throw new \Exception('Next Step not understand');
            }
        }
        $definitions = explode(';', $secondSibling->nodeValue);
        $sampleNode = null;
        $this->extractDefinition($definitions, $secondSibling, $sampleNode, $entry);

        $this->finishing($sampleNode, $secondSibling, $entry);
    }

    protected function finishing(&$sampleNode, \DOMNode $secondSibling, &$entry)
    {
        if ($sampleNode instanceof \DOMNode && $sampleNode->hasChildNodes()) {
            $found = false;
            /** @var $sampleChildNode \DOMNode */
            foreach ($sampleNode->childNodes as $sampleChildNode) {
                if ($sampleChildNode->nodeName === 'br') {
                    $this->result->add($entry);
                    if ($sampleNode->nextSibling->nodeName === 'b') {
                        $this->parseBody($sampleNode->nextSibling);
                        $found = true;
                    } else {
                        throw new \Exception('Next Step not understand');
                    }
                }
            }
            if (!$found) {
                $this->result->add($entry);
            }
        } else {

            $this->result->add($entry);
            if ($secondSibling->nextSibling->nodeName !== 'br') {
                if ($secondSibling->nextSibling->nodeName === 'b' && is_numeric(
                    $secondSibling->nextSibling->nodeValue
                )
                ) {
                    $nextEntry['entry'] = $entry['entry'];
                    $nextEntry['class'] = $entry['class'];
                    $nextEntry['syllable'] = $entry['syllable'];

                    $definitions = explode(';', $secondSibling->nextSibling->nextSibling->nodeValue);
                    $sampleNode = null;
                    $this->extractDefinition(
                        $definitions,
                        $secondSibling->nextSibling->nextSibling,
                        $sampleNode,
                        $nextEntry
                    );
                    $this->finishing($sampleNode, $secondSibling->nextSibling->nextSibling, $nextEntry);
                } else {
                    throw new \Exception('Next Step not understand');
                }
            } else {
                $this->checkTag($secondSibling->nextSibling, 'br');


                $this->parseRestDefinition($secondSibling->nextSibling);
            }
        }
    }

    protected function extractDefinition($definitions, \DOMNode $secondSibling, &$sampleNode, &$entry)
    {
        foreach ($definitions as $definition) {
            $definition = trim($definition);
            if ($definition !== '') {
                $explodeSample = explode(':', $definition);
                if (count($explodeSample) > 1) {
                    $sampleNode = $secondSibling->nextSibling;
                    if ($sampleNode->nodeName === 'i') {
                        $explodeSampleEntry = explode(';', trim($sampleNode->nodeValue));
                        if (strpos($explodeSampleEntry[0], '~') === false) {
                            $entry['sample'] = $entry['entry'] . ' ' . trim($explodeSampleEntry[0]);
                        } else {
                            $entry['sample'] = str_replace('~', $entry['entry'], $explodeSampleEntry[0]);
                        }
                        $entry['definition'][] = trim($explodeSample[0]);
                    }
                } else {
                    $entry['definition'][] = $explodeSample[0];
                }
            }
        }
    }

    protected function parseRestDefinition(\DOMNode $node)
    {
        if ($node->nextSibling instanceof \DOMNode) {
            $node = $node->nextSibling;
            if ($node->nodeName === '#text') {
                if ($node->nodeValue === '--') {
                    $this->parseInheritance($node);
                }
            } elseif($node->nodeName === 'b'){
                $this->parseBody($node);
            }else {
                throw new \Exception('Next Step not understand');
            }
        } else {
            throw new \Exception('Next Sibling not found.');
        }
    }

    protected function parseInheritance(\DOMNode $node)
    {
        $entrySibling = $node->nextSibling;
        if ($entrySibling->nodeName === 'i' || $entrySibling->nodeName === 'b') {
            $entryRaw = explode(',', $entrySibling->nodeValue);
            $trimEntry = trim($entryRaw[0]);
            if (is_numeric(substr($trimEntry, -1))) {
                if (strlen($trimEntry) > 1) {
                    $trimEntry = trim(substr($trimEntry, 0, strlen($trimEntry) - 1));
                } else {
                    $trimEntry = str_replace(
                        $this->result->get(0)['entry'],
                        '--',
                        $this->result->get(count($this->result) - 1)['entry']
                    );
                }
            }
            if (strpos($trimEntry, '--') === false) {
                $entry['entry'] = $this->result->get(0)['entry'] . ' ' . $trimEntry;
            } else {
                $entry['entry'] = str_replace('--', $this->result->get(0)['entry'], $trimEntry);
            }
            if (count($entryRaw) > 1) {
                $entry['class'] = trim($entryRaw[1]);
            }
            $definitionSibling = $entrySibling->nextSibling;
            if ($definitionSibling->nodeName === '#text') {
                $definitionRaw = explode(';', $definitionSibling->nodeValue);
                $trimDefinition = trim($definitionRaw[0]);
                if ($trimDefinition === '') {
                    $nextDefinitionSibling = $definitionSibling->nextSibling;
                    if ($nextDefinitionSibling->nodeName === 'i') {
                        $entry['discipline'] = trim($nextDefinitionSibling->nodeValue);
                        $definitionSibling = $nextDefinitionSibling->nextSibling;
                        if ($definitionSibling->nodeName === '#text') {
                            $definitionRaw = explode(';', $definitionSibling->nodeValue);
                            $trimDefinition = trim($definitionRaw[0]);
                        } else {
                            throw new \Exception('Sibling ' . $definitionSibling->nodeName . ' not expected');
                        }
                    } else {
                        throw new \Exception('Sibling ' . $nextDefinitionSibling->nodeName . ' not expected');
                    }
                }
                $explodeSample = explode(':', $trimDefinition);
                if (count($explodeSample) > 1) {
                    $sampleNode = $definitionSibling->nextSibling;
                    if ($sampleNode->nodeName === 'i') {
                        $explodeSampleEntry = explode(';', trim($sampleNode->nodeValue));
                        $entry['sample'] = str_replace('--', $this->result->get(0)['entry'], $explodeSampleEntry[0]);
                        $entry['definition'][] = trim($explodeSample[0]);
                        $this->result->add($entry);
                        if (count($explodeSampleEntry) > 1 && trim($explodeSampleEntry[1]) === '') {
                            $nextSampleNode = $sampleNode->nextSibling;
                            if ($nextSampleNode->nodeName === '#text' && trim($nextSampleNode->nodeValue) === '--') {
                                $this->parseInheritance($nextSampleNode);
                            }
                        }
                    }
                } else {
                    $entry['definition'][] = trim($explodeSample[0]);
                    $this->result->add($entry);
                    for ($i = 1; $i < count($definitionRaw); $i++) {
                        $trimDefinitionRaw = trim($definitionRaw[$i]);
                        if ($trimDefinitionRaw === '--' || $trimDefinitionRaw === '') {
                            $this->parseInheritance($definitionSibling);
                        } elseif (strpos($trimDefinitionRaw, $this->result->get(0)['entry']) !== false) {
                            $entry['definition'][] = $trimDefinitionRaw;
                        } else {
                            throw new \Exception('Trim Definition not understand');
                        }
                    }
                }

            } else {
                throw new \Exception('Definition Sibling not found. Found:' . $definitionSibling->nodeName);
            }
        } elseif ($entrySibling->nodeName === 'br') {
            $nextSibling = $entrySibling->nextSibling;
            if ($nextSibling->nodeName === '#text' && trim($nextSibling->nodeValue) === '--') {
                $this->parseInheritance($nextSibling);
            } elseif ($nextSibling->nodeName === 'b') {
                $this->parseBody($nextSibling);
            } else {
                throw new \Exception('Sibling ' . $nextSibling->nodeName . ' not expected');
            }
        } else {
            throw new \Exception('Sibling ' . $entrySibling->nodeName . ' not expected');
        }
    }
}
