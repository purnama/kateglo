<?php
/**
 *  Momoku Glue Stack Framework
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
 * and is licensed under the LGPL 3.0. For more information, see
 * <http://github.com/purnama/momoku>.
 *
 * @license <http://www.gnu.org/copyleft/lesser.html> LGPL 3.0
 * @link    http://github.com/purnama/momoku
 * @copyright Copyright (c) 2013 Momoku (http://github.com/purnama/momoku)
 */

namespace Momoku\Form\Annotation;

/**
 * Validator annotation
 *
 * Expects an associative array defining the validator.
 *
 * Typically, this includes the "name" with an associated string value
 * indicating the validator name or class, and optionally an "options" key
 * with an object/associative array value of options to pass to the
 * validator constructor.
 *
 * This annotation may be specified multiple times; validators will be added
 * to the validator chain in the order specified.
 *
 * @Annotation
 */
class Validator
{
    /**
     * @var array|string
     */
    protected $value;

    /**
     * Receive and process the contents of an annotation
     *
     * @param  array|string $data
     * @throws Exception\DomainException if a 'value' key is missing, or its value is not an array
     */
    public function __construct($data)
    {
        if (!isset($data['value']) || !is_array($data['value'])) {
            throw new Exception\DomainException(sprintf(
                '%s expects the annotation to define an array; received "%s"',
                get_class($this),
                isset($data['value']) ? gettype($data['value']) : 'null'
            ));
        }
        $this->value = $data['value'];
    }

    /**
     * Retrieve the validator specification
     *
     * @return null|array
     */
    public function getValidator()
    {
        return $this->value;
    }
}
