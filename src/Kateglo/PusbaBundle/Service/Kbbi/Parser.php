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

    /**
     * @var string
     */
    private $currentEntry;

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

        try {
            $this->loadHTML($dom, $content);
        } catch (\Exception $e) {
            //KBBI HTML is sometimes invalid. Add an i end tag before load.
            //If still not valid. throw the exceptions
            $this->loadHTML($dom, $content . '</i>');
        }

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

    protected function loadHTML(\DOMDocument $dom, $content)
    {
        set_error_handler(
            function ($number, $error) {
                if (preg_match('/^DOMDocument::loadHTML\(\): (.+)$/', $error, $m) === 1) {
                    restore_error_handler();
                    throw new \Exception($m[1]);
                }
            }
        );
        $dom->loadHTML($content);
        restore_error_handler();
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
                $entry['syllable'] = $this->trimNewLines($b->nodeValue);
                $entry['entry'] = str_ireplace('·', '', $entry['syllable']);
            }
        }

        if ($node->nextSibling->nodeName === 'i') {
            $firstSibling = $node->nextSibling;
        } else {
            $firstSibling = $node->nextSibling->nextSibling;
        }
        $this->checkTag($firstSibling, 'i');
        $entry['class'] = $this->trimNewLines($firstSibling->nodeValue);
        $this->currentEntry = $entry['entry'];

        if ($firstSibling->nextSibling->nodeName === 'b' && is_numeric(trim($firstSibling->nextSibling->nodeValue))) {
            $secondSibling = $firstSibling->nextSibling->nextSibling;
        } else {
            $secondSibling = $firstSibling->nextSibling;
        }
        $this->checkTag($secondSibling, '#text');
        if ($this->trimNewLines($secondSibling->nodeValue) === '') {
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
                    } elseif ($sampleNode->nextSibling->nodeName === '#text') {
                        $this->checkTag($sampleNode->nextSibling->nextSibling, 'br');
                        $this->parseBody($sampleNode->nextSibling->nextSibling->nextSibling);
                        $found = true;
                    } else {
                        throw new \Exception('Next Step not understand');
                    }
                }
            }
            if (!$found) {
                $this->result->add($entry);
                if ($sampleNode->nextSibling instanceof \DOMNode) {
                    if ($sampleNode->nextSibling->nodeName === '#text' && $this->trimNewLines(
                        $sampleNode->nextSibling->nodeValue
                    ) === ''
                    ) {
                        $sampleNode = $sampleNode->nextSibling;
                    }
                    if ($sampleNode->nextSibling->nodeName === 'b' && is_numeric(
                        trim($sampleNode->nextSibling->nodeValue)
                    )
                    ) {
                        $this->extractNextMeaning($sampleNode, $entry);
                    }
                }
            }
        } else {

            $this->result->add($entry);
            if ($secondSibling->nextSibling instanceof \DOMNode) {
                if ($secondSibling->nextSibling->nodeName !== 'br') {
                    if ($secondSibling->nextSibling->nodeName === 'b' && is_numeric(
                        trim($secondSibling->nextSibling->nodeValue)
                    )
                    ) {
                        $this->extractNextMeaning($secondSibling, $entry);
                    } else {
                        throw new \Exception('Next Step not understand');
                    }
                } else {
                    $this->checkTag($secondSibling->nextSibling, 'br');

                    $this->parseRestDefinition($secondSibling->nextSibling);

                }
            }
        }
    }

    /**
     * @param \DOMNode $secondSibling
     * @param $entry
     */
    protected function extractNextMeaning(\DOMNode $secondSibling, &$entry)
    {
        $nextEntry['entry'] = $entry['entry'];
        $nextEntry['class'] = $entry['class'];
        $nextEntry['syllable'] = $entry['syllable'];
        $this->currentEntry = $nextEntry['entry'];
        $definitionNode = $secondSibling->nextSibling->nextSibling;
        if ($definitionNode->nodeName === '#text' && $this->trimNewLines(
            $definitionNode->nodeValue
        ) === ''
        ) {
            $definitionNode = $definitionNode->nextSibling;
        }
        if ($definitionNode->nodeName === 'i') {
            $nextEntry['discipline'] = $definitionNode->nodeValue;
            $definitionNode = $definitionNode->nextSibling;
            $this->checkTag($definitionNode, '#text');
        }
        $definitions = explode(';', $definitionNode->nodeValue);
        $sampleNode = null;
        $this->extractDefinition(
            $definitions,
            $definitionNode,
            $sampleNode,
            $nextEntry
        );
        $this->finishing($sampleNode, $definitionNode, $nextEntry);
    }

    protected function extractDefinition($definitions, \DOMNode $secondSibling, &$sampleNode, &$entry)
    {
        foreach ($definitions as $definition) {
            $definition = $this->trimNewLines($definition);
            if ($definition !== '') {
                $explodeSample = explode(':', $definition);
                if (count($explodeSample) > 1) {
                    $sampleNode = $secondSibling->nextSibling;
                    if ($sampleNode->nodeName === 'i') {
                        $explodeSampleEntry = explode(';', $this->trimNewLines($sampleNode->nodeValue));
                        for ($i = 0; $i < count($explodeSampleEntry); $i++) {
                            if (strpos($explodeSampleEntry[$i], '~') === false) {
                                $sampleRaw = $this->trimNewLines(
                                    $entry['entry'] . ' ' . $this->trimNewLines($explodeSampleEntry[$i])
                                );
                            } else {
                                $sampleRaw = $this->trimNewLines(
                                    str_replace('~', $entry['entry'], $explodeSampleEntry[$i])
                                );
                            }
                            $explodeSampleRaw = explode(',', $sampleRaw);
                            if (count($explodeSampleRaw) > 1) {
                                $newEntry['class'] = $this->trimNewLines(array_pop($explodeSampleRaw));
                                $newEntry['entry'] = $this->trimNewLines(implode(', ', $explodeSampleRaw));
                                $this->currentEntry = $newEntry['entry'];
                                $newDefinition = $sampleNode->nextSibling;
                                $this->checkTag($newDefinition, '#text');
                                $newDefinitionRaw = explode(';', $newDefinition->nodeValue);
                                foreach ($newDefinitionRaw as $definitionRaw) {
                                    $newEntry['definition'][] = $this->trimNewLines($definitionRaw);
                                }
                                $this->result->add($newEntry);
                            } else {
                                $entry['sample'][] = $sampleRaw;
                            }
                        }
                        $entry['definition'][] = $this->trimNewLines($explodeSample[0]);
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
                if (trim($node->nodeValue) === '--') {
                    $this->parseInheritance($node);
                } elseif (trim($node->nodeValue) === '~') {
                    $this->parseInheritance($node);
                } else {
                    throw new \Exception('Next Step not understand');
                }
            } elseif ($node->nodeName === 'b') {
                $this->parseBody($node);
            } elseif ($node->nodeName === 'i') {
                $this->parseInheritance($node->previousSibling);
            } else {
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
            if (count($entryRaw) > 1) {
                $entry['class'] = $this->trimNewLines(array_pop($entryRaw));
            }
            $trimEntry = $this->trimNewLines(implode(', ', $entryRaw));
            if (is_numeric(substr($trimEntry, -1))) {
                if (strlen($trimEntry) > 1) {
                    $trimEntry = $this->trimNewLines(substr($trimEntry, 0, strlen($trimEntry) - 1));
                } else {
                    $trimEntry = $this->trimNewLines(
                        str_replace(
                            $this->result->get(0)['entry'],
                            '--',
                            $this->result->get(count($this->result) - 1)['entry']
                        )
                    );
                }
            }
            if (strpos($trimEntry, '--') === false && strpos($trimEntry, '~') === false) {

                if (strpos($this->trimNewLines($entrySibling->previousSibling->nodeValue), '~') !== false) {
                    $entry['entry'] = $this->currentEntry . ' ' . $trimEntry;
                } elseif (strpos($this->trimNewLines($entrySibling->previousSibling->nodeValue), '--') !== false) {
                    $entry['entry'] = $this->result->get(0)['entry'] . ' ' . $trimEntry;
                } else {
                    throw new \Exception('Next Step not understand');
                }

            } else {
                if (strpos($trimEntry, '--') !== false) {
                    $entry['entry'] = str_replace('--', $this->result->get(0)['entry'], $trimEntry);
                } elseif (strpos($trimEntry, '~') !== false) {
                    $entry['entry'] = str_replace(
                        '~',
                        $this->currentEntry,
                        $trimEntry
                    );
                } else {
                    throw new \Exception('Next Step not understand');
                }

            }
            $definitionSibling = $entrySibling->nextSibling;

            if ($definitionSibling->nodeName === 'i') {
                $entry['discipline'] = $this->trimNewLines($definitionSibling->nodeValue);
                $definitionSibling = $definitionSibling->nextSibling;
            }

            $this->getDefinitionSibling(
                $definitionSibling,
                $definitionRaw,
                $trimDefinition,
                $explodeSample
            );

            if (count($definitionRaw) === 1 && $trimDefinition !== '' && count($explodeSample) <= 1) {
                if ($definitionSibling->nextSibling->nodeName === 'br') {
                    if ($definitionSibling->nextSibling->nextSibling->nodeName === '#text' &&
                        $this->trimNewLines($definitionSibling->nextSibling->nextSibling->nodeValue) !== '--'
                    ) {
                        $definitionSibling = $definitionSibling->nextSibling->nextSibling;
                        $this->getDefinitionSibling(
                            $definitionSibling,
                            $definitionRaw,
                            $trimDefinition,
                            $explodeSample
                        );
                    } else {
                        throw new \Exception('Sibling ' . $definitionSibling->nextSibling->nextSibling->nodeName . ' not expected');
                    }
                } else {
                    throw new \Exception('Sibling ' . $definitionSibling->nextSibling->nodeName . ' not expected');
                }
            }
            if ($trimDefinition === '') {
                $nextDefinitionSibling = $definitionSibling->nextSibling;
                if ($nextDefinitionSibling->nodeName === 'i') {
                    $entry['discipline'] = $this->trimNewLines($nextDefinitionSibling->nodeValue);
                    $definitionSibling = $nextDefinitionSibling->nextSibling;
                    $this->getDefinitionSibling(
                        $definitionSibling,
                        $definitionRaw,
                        $trimDefinition,
                        $explodeSample
                    );
                } elseif ($nextDefinitionSibling->nodeName === 'b' && is_numeric(
                    $nextDefinitionSibling->nodeValue
                )
                ) {
                    $definitionSibling = $nextDefinitionSibling->nextSibling;
                    $this->getDefinitionSibling(
                        $definitionSibling,
                        $definitionRaw,
                        $trimDefinition,
                        $explodeSample
                    );
                } else {
                    throw new \Exception('Sibling ' . $nextDefinitionSibling->nodeName . ' not expected');
                }
            }
            if (count($explodeSample) > 1) {
                $sampleNode = $definitionSibling->nextSibling;
                if ($sampleNode->nodeName === 'i') {
                    $explodeSampleEntry = explode(';', $this->trimNewLines($sampleNode->nodeValue));
                    $entry['sample'][] = $this->trimNewLines(
                        str_replace('--', $this->result->get(0)['entry'], $explodeSampleEntry[0])
                    );
                    foreach ($explodeSample as $singleExplodeSample) {
                        $entry['definition'][] = $this->trimNewLines($singleExplodeSample);
                    }
                    $this->result->add($entry);
                    if (count($explodeSampleEntry) > 1 && $this->trimNewLines($explodeSampleEntry[1]) === '') {
                        $nextSampleNode = $sampleNode->nextSibling;
                        if ($nextSampleNode->nodeName === '#text' && $this->trimNewLines(
                            $nextSampleNode->nodeValue
                        ) === '--'
                        ) {
                            $this->parseInheritance($nextSampleNode);
                        }
                    }
                }
            } else {
                $entry['definition'][] = $this->trimNewLines($explodeSample[0]);
                $save = true;
                for ($i = 1; $i < count($definitionRaw); $i++) {
                    $trimDefinitionRaw = $this->trimNewLines($definitionRaw[$i]);
                    if ($trimDefinitionRaw === '--' || $trimDefinitionRaw === '~' || $trimDefinitionRaw === '') {
                        $this->result->add($entry);
                        $save = false;
                        $this->parseInheritance($definitionSibling);
                    } elseif ($trimDefinitionRaw !== '') {
                        $entry['definition'][] = $trimDefinitionRaw;
                    } else {
                        throw new \Exception('Trim Definition not understand');
                    }
                }

                if ($save) {
                    $this->result->add($entry);
                }
            }


        } elseif ($entrySibling->nodeName === 'br') {
            $nextSibling = $entrySibling->nextSibling;
            if ($nextSibling->nodeName === '#text' && $this->trimNewLines($nextSibling->nodeValue) === '--') {
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

    protected function getDefinitionSibling(
        \DOMNode &$definitionSibling,
        &$definitionRaw,
        &$trimDefinition,
        &$explodeSample
    ) {
        if ($definitionSibling->nodeName === '#text') {
            if ($definitionSibling->nextSibling->nodeName === 'br' && $definitionSibling->nextSibling->nextSibling->nodeName === '#text') {
                $concatDefinitionRaw = $definitionSibling->nodeValue . ' ' . $definitionSibling->nextSibling->nextSibling->nodeValue;
                $definitionRaw = explode(';', $concatDefinitionRaw);
                $definitionSibling = $definitionSibling->nextSibling->nextSibling;
            } else {
                $definitionRaw = explode(';', $definitionSibling->nodeValue);
            }
            $trimDefinition = $this->trimNewLines($definitionRaw[0]);
            $explodeSample = explode(':', $trimDefinition);
        } else {
            throw new \Exception('Sibling ' . $definitionSibling->nodeName . ' not expected');
        }
    }

    protected function trimNewLines($string)
    {
        return trim(preg_replace('/\s+/', ' ', $string));
    }
}
