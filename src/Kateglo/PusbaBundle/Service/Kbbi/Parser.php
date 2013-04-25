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
use Kateglo\PusbaBundle\Model\Entry;

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

    /**
     * @var Entry
     */
    private $entry;

    /**
     * @var array
     */
    private $definitions;

    /**
     * @var \DOMNode;
     */
    private $sampleNode;

    /**
     * @var \DOMNode
     */
    private $currentNode;

    /**
     * @var array
     */
    private $explodeSample;

    /**
     * @var array
     */
    private $explodeSampleEntry;

    /**
     * @var \DOMNode
     */
    private $definitionNode;

    /**
     * @var string
     */
    private $definitionString;

    /**
     * @var array
     */
    private $definitionRaw;

    /**
     * @var string
     */
    private $trimDefinition;

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

    private final function loadHTML(\DOMDocument $dom, $content)
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

    private final function checkTag(\DOMNode $node, $tagName)
    {
        if ($node->nodeName !== $tagName) {
            throw new \Exception('Expecting ' . $tagName . ' tag.');
        }
    }

    private final function parseBody(\DOMNode $node)
    {
        $this->checkTag($node, 'b');
        /** @var $b \DOMNode */
        $this->createEntry($node);

        $firstSibling = $this->findClassNode($node);

        $entryClass = $this->findSynonym($firstSibling);

        $this->entry->setClass($this->trimNewLines($entryClass));
        $this->currentEntry = $this->entry->getEntry();

        if ($this->isBoldAndNumeric($firstSibling->nextSibling)) {
            $this->currentNode = $firstSibling->nextSibling->nextSibling;
        } else {
            $this->currentNode = $firstSibling->nextSibling;
        }

        if ($this->isTextAndEmpty($this->currentNode)) {
            $this->currentNode = $this->currentNode->nextSibling;
            if ($this->isBoldAndNumeric($this->currentNode)) {
                $this->currentNode = $this->currentNode->nextSibling;
                if ($this->currentNode->nodeName !== '#text') {
                    throw new \Exception('Next Step not understand');
                }
            } else {
                throw new \Exception('Next Step not understand');
            }
        }

        $this->definitions = explode(';', $this->currentNode->nodeValue);
        $this->sampleNode = null;
        $this->extractDefinition();

        $this->finishing();
    }

    private final function createEntry(\DOMNode $node)
    {
        $this->entry = new Entry();
        foreach ($node->childNodes as $b) {
            if ($b->nodeName === '#text') {
                if ($this->entry->getSyllable() !== null && $this->entry->getSyllable() !== '') {
                    $this->entry->setSyllable($this->entry->getSyllable() . ' ' . $this->trimNewLines($b->nodeValue));
                } else {
                    $this->entry->setSyllable($this->trimNewLines($b->nodeValue));
                }
                $this->entry->setEntry(str_ireplace('·', '', $this->entry->getSyllable()));
            } else {
                if ($b->nodeName !== 'sup') {
                    throw new \Exception('Next Step not understand');
                }
            }
        }
    }

    private final function findClassNode(\DOMNode $node)
    {
        if ($node->nextSibling->nodeName === 'i') {
            $firstSibling = $node->nextSibling;
        } elseif ($this->isTextAndEmpty($node->nextSibling)
        ) {
            $firstSibling = $node->nextSibling->nextSibling;
        } else {
            throw new \Exception('Next Step not understand');
        }

        if ($firstSibling->nodeName === 'b') {
            $this->createEntry($firstSibling);
            $firstSibling = $this->findClassNode($firstSibling);
        }

        return $firstSibling;
    }


    private final function findSynonym(\DOMNode &$node)
    {
        $this->checkTag($node, 'i');
        $rawClass = $node->nodeValue;
        if (strpos($rawClass, ',') === strlen($rawClass) - 1) {
            $entry = clone($this->entry);
            $node = $this->findClassNode($node);
            $entry->getSynonyms()->add($this->entry->getEntry());
            $this->result->add($entry);
            $entryClass = $this->findSynonym($node);
        } elseif (strpos($rawClass, ',') === false) {
            $entryClass = $rawClass;
        } else {
            throw new \Exception('Next Step not understand');
        }

        return $entryClass;
    }

    private final function extractDefinition()
    {
        foreach ($this->definitions as $definition) {
            $definition = $this->trimNewLines($definition);
            if ($definition !== '') {
                $this->explodeSample = explode(':', $definition);
                if (count($this->explodeSample) > 1) {
                    $this->sampleNode = $this->currentNode->nextSibling;
                    if ($this->sampleNode->nodeName === 'br' && $this->sampleNode->nextSibling->nodeName === 'i') {
                        $this->currentNode = $this->currentNode->nextSibling;
                        $this->sampleNode = $this->sampleNode->nextSibling;
                    }
                    if ($this->isBoldAndNumeric($this->sampleNode)) {
                        $this->currentNode = $this->sampleNode->nextSibling;
                        $this->sampleNode = $this->currentNode->nextSibling;
                        if ($this->sampleNode->nodeName === 'br' && $this->sampleNode->nextSibling->nextSibling->nodeName === 'i') {
                            $this->currentNode = $this->currentNode->nextSibling->nextSibling;
                            $this->sampleNode = $this->sampleNode->nextSibling->nextSibling;
                        }
                    }
                    if ($this->sampleNode->nextSibling instanceof \DOMNode &&
                        $this->sampleNode->nodeName === 'i' &&
                        !$this->isTextAndEmpty($this->sampleNode->nextSibling) &&
                        $this->isTextAndEmpty($this->currentNode)
                    ) {
                        $this->sampleNode = $this->sampleNode->nextSibling->nextSibling;
                        $this->currentNode = $this->currentNode->nextSibling->nextSibling;
                    }
                    $this->extractSample();
                } else {
                    if (strpos($this->explodeSample[0], ',') === strlen($this->explodeSample[0]) - 1) {
                        $this->checkTag($this->currentNode->nextSibling, 'i');
                        $this->entry->getDefinitions()->add(
                            $this->explodeSample[0] . ' ' . $this->trimNewLines(
                                $this->currentNode->nextSibling->nodeValue
                            )
                        );
                        $this->currentNode = $this->currentNode->nextSibling;
                    } else {
                        $this->entry->getDefinitions()->add($this->explodeSample[0]);
                    }
                }
            }
        }
    }

    private final function extractSample()
    {
        if ($this->sampleNode->nodeName === 'i') {
            $this->explodeSampleEntry = explode(';', $this->trimNewLines($this->sampleNode->nodeValue));
            $this->checkNextSiblingForSample();
            for ($i = 0; $i < count($this->explodeSampleEntry); $i++) {
                if ($this->explodeSampleEntry[$i] === '' || $this->trimNewLines(
                    $this->explodeSampleEntry[$i]
                ) === '--'
                ) {
                    continue;
                }
                if (
                    strpos($this->explodeSampleEntry[$i], '~') === false
                    && strpos($this->explodeSampleEntry[$i], '- ') === false && strpos(
                        $this->explodeSampleEntry[$i],
                        ' -'
                    ) === false && strpos(
                        $this->explodeSampleEntry[$i],
                        '--'
                    ) === false
                ) {
                    $sampleRaw = $this->trimNewLines(
                        $this->entry->getEntry() . ' ' . $this->trimNewLines($this->explodeSampleEntry[$i])
                    );
                } else {
                    if (strpos($this->explodeSampleEntry[$i], ' -') === strlen($this->explodeSampleEntry[$i]) - 2) {
                        $sampleRaw = $this->trimNewLines(
                            str_replace(
                                ' -',
                                ' ' . $this->entry->getEntry(),
                                str_replace(
                                    '--',
                                    $this->entry->getEntry() . ' ',
                                    str_replace('~', $this->entry->getEntry(), $this->explodeSampleEntry[$i])
                                )
                            )
                        );
                    } else {
                        $sampleRaw = $this->trimNewLines(
                            str_replace(
                                '- ',
                                $this->entry->getEntry() . ' ',
                                str_replace(
                                    '--',
                                    $this->entry->getEntry() . ' ',
                                    str_replace('~', $this->currentEntry, $this->explodeSampleEntry[$i])
                                )
                            )
                        );
                    }
                }
                $explodeSampleRaw = explode(',', $sampleRaw);
                if (count($explodeSampleRaw) > 1 && strpos(
                    $explodeSampleRaw[count($explodeSampleRaw) - 1],
                    ' '
                ) === 0 && strlen(
                    $explodeSampleRaw[count($explodeSampleRaw) - 1]
                ) <= 5 && $this->sampleNode->nextSibling->nodeName === '#text'
                ) {
                    $newEntry = new Entry();
                    $newEntry->setDiscipline($this->trimNewLines(array_pop($explodeSampleRaw)));
                    $newEntry->setEntry($this->trimNewLines(implode(', ', $explodeSampleRaw)));
                    $this->currentEntry = $newEntry->getEntry();
                    $this->definitionNode = $this->sampleNode->nextSibling;
                    $this->checkTag($this->definitionNode, '#text');
                    $newDefinitionRaw = explode(';', $this->definitionNode->nodeValue);
                    foreach ($newDefinitionRaw as $definitionRaw) {
                        $newEntry->getDefinitions()->add($this->trimNewLines($definitionRaw));
                    }
                    $this->result->add($newEntry);
                    $this->definitionNode = $this->sampleNode;
                } else {
                    $this->entry->getSamples()->add($sampleRaw);
                    $this->currentNode = $this->sampleNode;
                    $this->definitionNode = $this->sampleNode;
                }
            }
            $this->entry->getDefinitions()->add($this->trimNewLines($this->explodeSample[0]));
        } elseif ($this->isBoldAndNumeric($this->sampleNode)) {
            $this->entry->getDefinitions()->add($this->trimNewLines($this->explodeSample[0]));
            $this->sampleNode = $this->sampleNode->previousSibling;
        } else {
            throw new \Exception('Next Step not understand');
        }
    }

    private final function checkNextSiblingForSample()
    {
        if ($this->explodeSampleEntry[count($this->explodeSampleEntry) - 1] !== '') {
            if ($this->sampleNode->nextSibling instanceof \DOMNode && $this->sampleNode->nextSibling->nodeName === '#text') {
                if ($this->trimNewLines($this->sampleNode->nextSibling->nodeValue) === ';') {
                    $this->explodeSampleEntry[] = '';
                    $this->currentNode = $this->currentNode->nextSibling;
                    $this->sampleNode = $this->sampleNode->nextSibling;
                } elseif (strpos($this->sampleNode->nodeValue, ',') === strlen($this->sampleNode->nodeValue) - 1) {
                    $this->explodeSampleEntry[] = $this->sampleNode->nextSibling->nodeValue;
                    $implodeSampleEntry = implode(' ', $this->explodeSampleEntry);
                    $this->explodeSampleEntry = explode(';', $implodeSampleEntry);
                    $this->currentNode = $this->currentNode->nextSibling->nextSibling;
                    $this->sampleNode = $this->sampleNode->nextSibling;
                    $this->checkNextSiblingForSample();
                }
            }

        }

    }

    private final function finishing()
    {
        if ($this->currentNode instanceof \DOMNode && $this->currentNode->hasChildNodes()) {
            $found = false;
            /** @var $sampleChildNode \DOMNode */
            $childNode = $this->currentNode;
            for ($i = 1; $i < $childNode->childNodes->length; $i++) {
                $sampleChildNode = $childNode->childNodes->item($i);
                if ($sampleChildNode->nodeName === 'br') {
                    $this->result->add($this->entry);
                    if ($this->currentNode->nextSibling->nodeName === 'b') {
                        if ($sampleChildNode->nextSibling instanceof \DOMNode && $sampleChildNode->nextSibling->nodeName === '#text' && $this->trimNewLines(
                            $sampleChildNode->nextSibling->nodeValue
                        ) === '--'
                        ) {
                            $this->parseInheritance($this->currentNode);
                        } else {
                            $this->parseBody($this->currentNode->nextSibling);
                        }
                        $found = true;
                    } elseif ($this->currentNode->nextSibling->nodeName === '#text') {
                        if ($this->currentNode->nextSibling->nodeValue === '--' || $this->currentNode->nextSibling->nodeValue === '~') {
                            $this->checkTag($this->currentNode->nextSibling->nextSibling, 'b');
                            $this->parseInheritance($this->currentNode->nextSibling);
                        } elseif ($this->currentNode->nextSibling->nextSibling === 'br') {
                            $this->parseBody($this->currentNode->nextSibling->nextSibling->nextSibling);
                        } elseif ($this->trimNewLines($this->currentNode->nextSibling->nodeValue) === '~') {
                            $this->parseInheritance($this->currentNode->nextSibling);
                        } else {
                            $found = true;
                            continue;
                        }
                        $found = true;
                    } else {
                        throw new \Exception('Next Step not understand');
                    }
                } elseif ($this->currentNode->nextSibling instanceof \DOMNode && $sampleChildNode->nodeName === '#text' && $this->currentNode->nextSibling->nodeName !== 'b') {
                    $entryRaw = explode(',', $sampleChildNode->nodeValue);
                    $this->entry = new Entry();
                    $this->definitionRaw = null;
                    $this->extractInheritance($this->currentNode, $entryRaw);
                }
            }
            if (!$found) {
                $this->result->add($this->entry);
                if ($this->currentNode->nextSibling instanceof \DOMNode) {
                    if ($this->isTextAndEmpty($this->currentNode->nextSibling)) {
                        $this->currentNode = $this->currentNode->nextSibling;
                    }
                    if ($this->currentNode->nextSibling->nodeName === 'b') {
                        if (is_numeric(trim($this->currentNode->nextSibling->nodeValue))) {
                            $this->extractNextMeaning();
                        } else {
                            $this->parseBody($this->currentNode->nextSibling);
                        }
                    }
                }
            }
        } else {

            $this->result->add($this->entry);
            if ($this->currentNode->nextSibling instanceof \DOMNode) {
                if ($this->currentNode->nextSibling->nodeName !== 'br') {
                    if ($this->isBoldAndNumeric($this->currentNode->nextSibling)) {
                        $this->extractNextMeaning();
                    } elseif ($this->isTextAndEmpty($this->currentNode->nextSibling) ||
                        $this->trimNewLines($this->currentNode->nextSibling->nodeValue) === ';'
                    ) {
                        $this->currentNode = $this->currentNode->nextSibling;
                        if ($this->currentNode->nextSibling->nodeName === 'br') {
                            $this->currentNode = $this->currentNode->nextSibling;
                        }
                        if ($this->currentNode->nextSibling->nodeName === 'b'
                        ) {
                            if (is_numeric(
                                trim($this->currentNode->nextSibling->nodeValue)
                            )
                            ) {
                                $this->extractNextMeaning();
                            } else {
                                $this->parseBody($this->currentNode->nextSibling);
                            }
                        } else {
                            throw new \Exception('Next Step not understand');
                        }
                    } elseif ($this->currentNode->nextSibling->nodeName === '#text' && $this->trimNewLines(
                        $this->currentNode->nextSibling->nodeValue
                    ) === ';'
                    ) {
                        $br = $this->currentNode->nextSibling->nextSibling;
                        $this->checkTag($br, 'br');
                        $this->parseBody($br->nextSibling);
                    } else {
                        throw new \Exception('Next Step not understand');
                    }
                } else {
                    $this->checkTag($this->currentNode->nextSibling, 'br');

                    $this->parseRestDefinition($this->currentNode->nextSibling);

                }
            }
        }
    }

    private final function parseInheritance(\DOMNode $node)
    {
        $entrySibling = $node->nextSibling;
        if ($entrySibling->nodeName === 'i' || $entrySibling->nodeName === 'b') {
            $entryRaw = explode(',', $entrySibling->nodeValue);
            $this->entry = new Entry();
            $this->definitionRaw = null;
            $this->extractInheritance($entrySibling, $entryRaw);
        } elseif ($entrySibling->nodeName === 'br') {
            $nextSibling = $entrySibling->nextSibling;
            if (($nextSibling->nodeName === '#text' || $nextSibling->nodeName === 'i') && $this->trimNewLines(
                $nextSibling->nodeValue
            ) === '--'
            ) {
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

    private final function extractInheritance(\DOMNode $entrySibling, $entryRaw)
    {
        if (count($entryRaw) > 1) {
            $this->entry->setClass($this->trimNewLines(array_pop($entryRaw)));
        }
        $trimEntry = $this->trimNewLines(implode(', ', $entryRaw));
        if (is_numeric(substr($trimEntry, -1))) {
            if (strlen($trimEntry) > 1) {
                $trimEntry = $this->trimNewLines(substr($trimEntry, 0, strlen($trimEntry) - 1));
            } else {
                $trimEntry = $this->trimNewLines(
                    str_replace(
                        $this->result->get(0)->getEntry(),
                        '--',
                        $this->result->get(count($this->result) - 1)->getEntry()
                    )
                );
            }
        }
        if (strpos($trimEntry, '--') === false && strpos($trimEntry, '~') === false && strpos(
            $trimEntry,
            '- '
        ) === false
        ) {

            if (strpos($this->trimNewLines($entrySibling->previousSibling->nodeValue), '~') !== false) {
                $this->entry->setEntry($this->currentEntry . ' ' . $trimEntry);
            } elseif (strpos($this->trimNewLines($entrySibling->previousSibling->nodeValue), '--') !== false) {
                $this->entry->setEntry($this->result->get(0)->getEntry() . ' ' . $trimEntry);
            } elseif ($this->trimNewLines($trimEntry) !== '') {
                $this->entry->setEntry($trimEntry);
            } else {
                throw new \Exception('Next Step not understand');
            }

        } else {
            if (strpos($trimEntry, '--') !== false) {
                $this->entry->setEntry(str_replace('--', $this->result->get(0)->getEntry(), $trimEntry));
            } elseif (strpos($trimEntry, '~') !== false) {
                $this->entry->setEntry(
                    str_replace(
                        '~',
                        $this->currentEntry,
                        $trimEntry
                    )
                );
            } elseif (strpos($trimEntry, '- ') !== false) {
                $this->entry->setEntry(
                    str_replace(
                        '- ',
                        $this->currentEntry . ' ',
                        $trimEntry
                    )
                );
            } else {
                throw new \Exception('Next Step not understand');
            }

        }
        $this->definitionNode = $entrySibling->nextSibling;
        if ($this->isTextAndEmpty($this->definitionNode)) {
            $this->definitionNode = $this->definitionNode->nextSibling;
        }
        if ($this->isBoldAndNumeric($this->definitionNode)) {
            $this->definitionNode = $this->definitionNode->nextSibling;
        }
        if ($this->definitionNode->nodeName === 'i') {
            $this->entry->setDiscipline($this->trimNewLines($this->definitionNode->nodeValue));
            $this->definitionNode = $this->definitionNode->nextSibling;
        }

        $this->getDefinitionSibling();

        $this->checkDefinitionSibling();

        if ($this->trimDefinition === '') {
            $nextDefinitionSibling = $this->definitionNode->nextSibling;
            if ($nextDefinitionSibling->nodeName === 'i') {
                $this->entry->setDiscipline($this->trimNewLines($nextDefinitionSibling->nodeValue));
                $this->definitionNode = $nextDefinitionSibling->nextSibling;
                $this->getDefinitionSibling();
            } elseif ($this->isBoldAndNumeric($nextDefinitionSibling)) {
                $this->definitionNode = $nextDefinitionSibling->nextSibling;
                $this->getDefinitionSibling();
            } else {
                throw new \Exception('Sibling ' . $nextDefinitionSibling->nodeName . ' not expected');
            }
        }
        if (count($this->explodeSample) > 1) {
            $this->sampleNode = $this->definitionNode->nextSibling;
            if ($this->sampleNode->nodeName === 'i') {
                $this->explodeSampleEntry = explode(';', $this->trimNewLines($this->sampleNode->nodeValue));

                if (strpos($this->explodeSampleEntry[0], '~') === false && strpos(
                    $this->explodeSampleEntry[0],
                    '- '
                ) === false &&
                    strpos($this->explodeSampleEntry[0], '--') === false
                ) {
                    $this->entry->getSamples()->add($this->currentEntry . ' ' . $this->explodeSampleEntry[0]);
                } else {
                    $this->entry->getSamples()->add(
                        $this->trimNewLines(
                            str_replace(
                                '~',
                                $this->currentEntry,
                                str_replace(
                                    '- ',
                                    $this->result->get(0)->getEntry() . ' ',
                                    str_replace('--', $this->result->get(0)->getEntry(), $this->explodeSampleEntry[0])
                                )
                            )
                        )
                    );
                }
                foreach ($this->explodeSample as $singleExplodeSample) {
                    if ($this->trimNewLines($singleExplodeSample) !== '' && $this->trimNewLines(
                        $singleExplodeSample
                    ) !== '~'
                    ) {
                        $this->entry->getDefinitions()->add($this->trimNewLines($singleExplodeSample));
                    }
                }
                $this->result->add($this->entry);
                if (count($this->explodeSampleEntry) > 1 && $this->trimNewLines(
                    $this->explodeSampleEntry[count($this->explodeSampleEntry) - 1]
                ) === ''
                ) {
                    $nextSampleNode = $this->sampleNode->nextSibling;
                    if ($nextSampleNode->nodeName === '#text' && $this->trimNewLines(
                        $nextSampleNode->nodeValue
                    ) === '--'
                    ) {
                        $this->parseInheritance($nextSampleNode);
                    } elseif ($nextSampleNode->nodeName === 'b') {
                        $this->parseBody($nextSampleNode);
                    } else {
                        throw new \Exception('Next Step not understand');
                    }
                } elseif ($this->sampleNode->nextSibling->nodeName === '#text') {
                    $nextSampleNodeExplode = explode(';', $this->sampleNode->nextSibling->nodeValue);
                    if (count($nextSampleNodeExplode) > 1 && ($this->trimNewLines(
                        $nextSampleNodeExplode[1]
                    ) === '--' || $this->trimNewLines(
                        $nextSampleNodeExplode[1]
                    ) === '~')
                    ) {
                        $this->parseInheritance($this->sampleNode->nextSibling);
                    } else {
                        throw new \Exception('Sibling ' . $nextDefinitionSibling->nodeName . ' not expected');
                    }
                }
            }
        } else {
            $this->entry->getDefinitions()->add($this->trimNewLines($this->explodeSample[0]));
            $save = true;
            for ($i = 1; $i < count($this->definitionRaw); $i++) {
                $trimDefinitionRaw = $this->trimNewLines($this->definitionRaw[$i]);
                $this->explodeSample = explode(':', $trimDefinitionRaw);
                if ($trimDefinitionRaw === '--' || $trimDefinitionRaw === '~' || $trimDefinitionRaw === '') {
                    $this->result->add($this->entry);
                    $save = false;
                    $this->parseInheritance($this->definitionNode);
                } elseif ($trimDefinitionRaw !== '') {
                    if (count($this->explodeSample) > 1) {
                        $this->currentNode = $this->definitionNode;
                        $this->definitionNode = $this->definitionNode->nextSibling;
                        $this->sampleNode = $this->definitionNode;
                        $this->extractSample();
                        if ($this->definitionNode->nextSibling->nodeName === '#text' && strpos(
                            $this->trimNewLines($this->definitionNode->nextSibling->nodeValue),
                            '~'
                        ) !== false && $this->definitionNode->nextSibling->nextSibling->nodeName === 'b'
                        ) {
                            $this->result->add($this->entry);
                            $save = false;
                            $this->parseInheritance($this->definitionNode->nextSibling);
                        }
                    } else {
                        $this->entry->getDefinitions()->add($trimDefinitionRaw);
                    }
                } else {
                    throw new \Exception('Trim Definition not understand');
                }
            }

            if ($save) {
                $this->result->add($this->entry);
            }
        }
    }

    private final function getDefinitionSibling()
    {
        if ($this->definitionNode->nodeName === '#text') {
            if ($this->definitionNode->nextSibling instanceof \DOMNode &&
                $this->definitionNode->nextSibling->nodeName === 'br' &&
                $this->definitionNode->nextSibling->nextSibling->nodeName === '#text'
            ) {
                $concatDefinitionRaw = $this->definitionNode->nodeValue . ' ' .
                    $this->definitionNode->nextSibling->nextSibling->nodeValue;
                $this->definitionRaw = explode(';', $concatDefinitionRaw);
                $this->definitionNode = $this->definitionNode->nextSibling->nextSibling;
            } elseif (count($this->definitionRaw) > 0) {
                $concatDefinitionRaw = $this->definitionRaw[0] . ' ' . $this->definitionNode->nodeValue;
                $this->definitionRaw = explode(';', $concatDefinitionRaw);
            } else {
                $this->definitionRaw = explode(';', $this->definitionNode->nodeValue);
            }

        } elseif ($this->definitionNode->nodeName === 'i') {
            if (count($this->definitionRaw) === 1) {
                $concatDefinitionRaw = $this->definitionRaw[0] . ' ' . $this->definitionNode->nodeValue;
                $this->definitionRaw = explode(';', $concatDefinitionRaw);
            } elseif (count($this->definitionRaw) > 1 && $this->definitionRaw[count($this->definitionRaw) - 1] === '') {
                $this->definitionRaw[count($this->definitionRaw) - 1] = $this->definitionNode->nodeValue;
                $concatDefinitionRaw = implode('; ', $this->definitionRaw);
                $this->definitionRaw = explode(';', $concatDefinitionRaw);
            } else {
                throw new \Exception('Next Step not understand');
            }
        } else {
            throw new \Exception('Sibling ' . $this->definitionNode->nodeName . ' not expected');
        }
        for ($i = 0; $i < count($this->definitionRaw); $i++) {
            $this->definitionRaw[$i] = $this->trimNewLines($this->definitionRaw[$i]);
        }
        if (count(
            $this->definitionRaw
        ) > 1 && $this->definitionRaw[count($this->definitionRaw) - 1] === ''
        ) {
            if (($this->definitionNode->nextSibling->nodeName === '#text' && $this->trimNewLines(
                $this->definitionNode->nextSibling->nodeValue
            ) === '--')
            ) {
                $this->definitionRaw[count($this->definitionRaw) - 1] = $this->definitionNode->nextSibling->nodeValue;
                $this->definitionNode = $this->definitionNode->nextSibling;
            } elseif ($this->definitionNode->nextSibling->nodeName === 'i' && strpos(
                $this->definitionNode->nextSibling->nodeValue,
                ';'
            ) !== false
            ) {
                $this->definitionNode = $this->definitionNode->nextSibling;
                $this->getDefinitionSibling();
            }
        }

        $this->trimDefinition = $this->trimNewLines($this->definitionRaw[0]);
        if (strpos($this->trimDefinition, '--') !== false &&
            strpos($this->trimDefinition, '--') === strlen($this->trimDefinition) - 2
        ) {
            $strReplaceDefinitionRaw = str_replace(' --', '; --', $this->trimDefinition);
            $this->definitionRaw = explode(';', $strReplaceDefinitionRaw);
            $this->trimDefinition = $this->trimNewLines($this->definitionRaw[0]);
        }
        $this->explodeSample = explode(':', $this->trimDefinition);
        $this->checkDefinitionSibling();
    }


    private final function checkDefinitionSibling()
    {
        if ($this->definitionNode->nextSibling instanceof \DOMNode && count(
            $this->definitionRaw
        ) === 1 && $this->trimDefinition !== '' && count($this->explodeSample) <= 1
        ) {
            if ($this->definitionNode->nextSibling->nodeName === 'br') {
                if ($this->definitionNode->nextSibling->nextSibling->nodeName === '#text' &&
                    $this->trimNewLines($this->definitionNode->nextSibling->nextSibling->nodeValue) !== '--'
                ) {
                    $this->definitionNode = $this->definitionNode->nextSibling->nextSibling;
                    $this->getDefinitionSibling();
                } else {
                    throw new \Exception('Sibling ' . $this->definitionNode->nextSibling->nextSibling->nodeName .
                        ' not expected');
                }
            } elseif ($this->definitionNode->nextSibling->nodeName === 'i' ||
                $this->definitionNode->nextSibling->nodeName === '#text'
            ) {
                $this->definitionNode = $this->definitionNode->nextSibling;
                $this->getDefinitionSibling();
            } else {
                throw new \Exception('Sibling ' . $this->definitionNode->nextSibling->nodeName . ' not expected');
            }
        }
    }

    private final function extractNextMeaning()
    {
        $nextEntry = new Entry();
        $nextEntry->setEntry($this->entry->getEntry());
        $nextEntry->setClass($this->entry->getClass());
        $nextEntry->setSyllable($this->entry->getSyllable());
        $this->currentEntry = $nextEntry->getEntry();
        $this->definitionNode = $this->currentNode->nextSibling->nextSibling;
        if ($this->isTextAndEmpty($this->definitionNode)) {
            $this->definitionNode = $this->definitionNode->nextSibling;
        }
        if ($this->definitionNode->nodeName === 'i') {
            $nextEntry->setDiscipline($this->definitionNode->nodeValue);
            $this->definitionNode = $this->definitionNode->nextSibling;
            $this->checkTag($this->definitionNode, '#text');
        }
        $this->entry = $nextEntry;
        $this->definitionString = null;
        $this->extractEntryDefinition();
        $this->definitions = explode(';', $this->definitionString);
        $this->sampleNode = null;
        $this->extractDefinition();
        $this->currentNode = $this->definitionNode;
        $this->finishing();
    }

    private final function extractEntryDefinition()
    {
        if (!$this->definitionString !== null && $this->definitionString !== '') {
            $this->definitionString = $this->trimNewLines(
                $this->definitionString . ' ' . $this->trimNewLines($this->definitionNode->nodeValue)
            );
        } else {
            $this->definitionString = $this->trimNewLines($this->definitionNode->nodeValue);
        }
        if (strpos($this->definitionString, ';') !== strlen($this->definitionString) - 1 &&
            strpos($this->definitionString, ':') === false
        ) {
            if ($this->definitionNode->nextSibling instanceof \DOMNode) {
                if ($this->definitionNode->nextSibling->nodeName !== 'b') {
                    if ($this->definitionNode->nextSibling->nodeName === 'i' || $this->definitionNode->nextSibling->nodeName === '#text') {
                        $this->definitionNode = $this->definitionNode->nextSibling;
                        $this->extractEntryDefinition();
                    } elseif ($this->definitionNode->nextSibling->nodeName === 'br' && $this->definitionNode->nextSibling->nextSibling->nodeName === '#text') {
                        $this->definitionNode = $this->definitionNode->nextSibling->nextSibling;
                        $this->extractEntryDefinition();
                    }
                }
            }
        }
    }

    private final function parseRestDefinition(\DOMNode $node)
    {
        if ($node->nextSibling instanceof \DOMNode) {
            $node = $node->nextSibling;
            if ($node->nodeName === '#text') {
                if ($this->trimNewLines($node->nodeValue) === '--') {
                    $this->parseInheritance($node);
                } elseif ($this->trimNewLines($node->nodeValue) === '~') {
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

    protected function trimNewLines($string)
    {
        return trim(preg_replace('/\s+/', ' ', $string));
    }


    /**
     * @param \DOMNode $node
     * @return bool
     */
    private function isBoldAndNumeric(\DOMNode $node)
    {
        return $node->nodeName === 'b' && is_numeric(
            $this->trimNewLines($node->nodeValue)
        );
    }

    /**
     * @param \DOMNode $node
     * @return bool
     */
    protected function isTextAndEmpty(\DOMNode $node)
    {
        return $node->nodeName === '#text' && $this->trimNewLines(
            $node->nodeValue
        ) === '';
    }
}
