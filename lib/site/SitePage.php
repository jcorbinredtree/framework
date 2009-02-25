<?php

/**
 * SitePage definition
 *
 * PHP version 5
 *
 * LICENSE: The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 *
 * The Original Code is Red Tree Systems Code.
 *
 * The Initial Developer of the Original Code is Red Tree Systems, LLC. All Rights Reserved.
 *
 * @category     UI
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2009 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

require_once 'lib/util/CallbackManager.php';
require_once 'lib/site/SitePageHeaders.php';
require_once 'lib/ui/TemplateSystem.php';

/**
 * Describes a page in a site (amazing)
 *
 * Basic page, manages things such as page "data" which is named arbitrary
 * mixed values and "buffers", which is a primitive form of page content on the
 * way out to the client.
 *
 * @package Site
 */
class SitePage extends CallbackManager
{
    /**
     * The component responsible for this page, if any
     * @var Component
     */
    public $component;

    /**
     * Outgoing HTTP Header interface
     *
     * @var SitePageHeaders
     */
    public $headers;

    /**
     * The mime type of the page
     *
     * @var string
     */
    protected $type;

    /**
     * The template resource sting to use to render this page
     *
     * The default implementation defaults to "page/type.xml" where the "type"
     * portion is the $type property with all '/'s replaced with '_'s.
     *
     * This can be null if a subclass does not wish to use a template-based
     * approach
     *
     * @var string
     * @see onRender
     */
    protected $template;

    /**
     * Named page content buffers such as 'head', 'top', 'left', etc.
     */
    private $buffers;

    /**
     * The buffer currently being rendered
     */
    private $renderingBuffer = null;

    /**
     * Arbitrary page data by group name such as 'breadCrumbs' or
     * 'topNavigation'.
     *
     * The distinction between buffers and data is that buffers are clearly
     * defined as equivilent to string data and rendered at the end of the
     * request; however data can be absolutely anything.
     */
    private $data;

    /**
     * Constructor
     *
     * Creates a new SitePage.
     *
     * @param type string the type of the page, defaults to 'text/plain'
     * @param template the template resource string used to render this page
     * if set to the false value, then the $template property will not be set
     * @see $template
     */
    public function __construct($type='text/plain', $template=null)
    {
        $this->buffers = array();
        $this->data = array();
        $this->type = $type;
        if (isset($template)) {
            if ($template === false) {
                $this->template = null;
            } else {
                $this->template = $template;
            }
        } else {
            $this->template = sprintf('page/%s.xml',
                preg_replace('/\//', '_', $this->type)
            );
        }
        $this->headers = new SitePageHeaders();
        $this->headers->setContentType($this->type);
    }

    /**
     * Gets the mime type of the page
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Tests whether the given mime type is compatible with this page
     *
     * @param t string
     * @return boolean
     */
    public function compatibleType($t)
    {
        return $t == $this->type;
    }

    /**
     * Adds an item to the named buffer
     *
     * @param name the name of the buffer, e.g. head, top, left, etc
     * @param content mixed a renderable item
     *
     * @return void
     *
     * @see renderBufferedItem
     */
    public function addToBuffer($name, &$content)
    {
        if (isset($this->renderingBuffer) && $this->renderingBuffer == $name) {
            throw new RuntimeException(
                "Cannot add to page buffer $name while rendering it"
            );
        }
        if ($content === null) {
            return;
        }
        if (! array_key_exists($name, $this->buffers)) {
            $this->buffers[$name] = array();
        }
        array_push($this->buffers[$name], $content);
    }

    /**
     * Renders and returns the named buffer
     *
     * @param name the name of the buffer, e.g. head, top, left, etc
     * @param asArray boolean if true, an array is returned containing each
     * item from the buffer rendered, otherwise the concatenation of this array
     * is returned.
     * @param flush boolean, if true call clearBuffer right before returning
     *
     * @return string or array if name exists, null otherwise
     *
     * @see renderBufferedItem
     */
    public function getBuffer($name, $asArray=false, $flush=false)
    {
        if (isset($this->renderingBuffer)) {
            throw new RuntimeException(
                'SitePage->getBuffer called recursively '.
                "$name inside of $this->renderingBuffer"
            );
        }

        if (! array_key_exists($name, $this->buffers)) {
            return null;
        }

        $this->renderingBuffer = $name;

        if ($asArray) {
            $ret = array();
            foreach ($this->buffers[$name] as &$item) {
                array_push($ret,
                    $this->renderBufferedItem($item)
                );
            }
        } else {
            $ret = $this->renderBufferedItem(
                $this->buffers[$name], array($name)
            );
        }

        $this->renderingBuffer = null;

        if ($flush) {
            $this->clearBuffer($name);
        }
        return $ret;
    }

    /**
     * Emptys the named buffer.
     * Doesn't return anything, call getBuffer first if you want that
     *
     * @param name string
     * @return void
     */
    public function clearBuffer($name)
    {
        if (array_key_exists($name, $this->buffers)) {
            $this->buffers[$name] = array();
        }
    }

    /**
     * Calls processBuffer on each defined buffer
     *
     * @return void
     */
    public function processBuffers()
    {
        foreach (array_keys($this->buffers) as $name) {
            $this->processBuffer($name);
        }
    }

    /**
     * Processes the named buffer
     *
     * This turns each item in the buffer into its string representation and
     * throws away the old non-string data. The buffer still remains as an array
     * of items, but each item will now be just a string.
     *
     * @param name string the buffer
     *
     * @return void
     */
    public function processBuffer($name)
    {
        if (! array_key_exists($name, $this->buffers)) {
            throw new InvalidArgumentException('Invaild buffer name');
        }
        $this->buffers[$name] = $this->getBuffer($name, true);
    }

    /**
     * Renders a buffered item
     *
     * @param mixed the item, can be any of:
     * - a string
     * - an object derived from BufferedObject, call getBuffer and concatenate
     * - an object that implements __tostring, convert to string and concatenate
     * - any other object, a string "[Objcet of type CLASS]"
     * - an array, each array element will be passed through renderBufferedItem
     *   and concatenated.
     * - a callable, call the callable, passing it args as arguments and then
     *   pass its return through renderBUfferedItem.
     *
     * @return string
     */
    private function renderBufferedItem(&$item, $args=array())
    {
        try {
            if (is_array($item)) {
                $r = '';
                foreach ($item as &$i) {
                    $r .= $this->renderBufferedItem($i, $args);
                }
                return $r;
            } elseif (is_object($item)) {
                if (is_a($item, 'BufferedObject')) {
                    return $item->getBuffer();
                } elseif (is_a($item, 'PHPSTLTemplate')) {
                    return $item->render();
                } elseif (method_exists($item, '__tostring')) {
                    return (string) $item;
                } else {
                    return "[Object of type ".get_class($item)."]";
                }
            } elseif (is_callable($item)) {
                $ret = call_user_func_array($item, $args);
                return $this->renderBufferedItem($ret, $args);
            } else {
                return $item;
            }
        } catch (Exception $e) {
            return (string) $e;
        };
    }

    /**
     * Sets a data item
     *
     * This implements a singular data item, see addData if the item should
     * be an array.
     *
     * @param name string
     * @param value string if null, unsets the item
     * @return void
     */
    public function setData($name, $value)
    {
        if (isset($value)) {
            $this->data[$name] = $value;
        } elseif (array_key_exists($name, $this->data)) {
            unset($this->data[$name]);
        }
    }

    /**
     * Adds a data item to the page
     * Similar in spirit to addToBuffer but with less semantics
     *
     * @param name string
     * @param item mixed
     * @return void
     */
    public function addData($name, &$item)
    {
        if ($item === null) {
            return;
        }
        if (! array_key_exists($name, $this->data)) {
            $this->data[$name] = array();
        }
        array_push($this->data[$name], $item);
    }

    /**
     * Tests whether the named data item exists
     *
     * @param name string
     * @return boolean true if a call to getData($name) would return non-null
     */
    public function hasData($name)
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * Returns the named data item
     *
     * @param name string
     * @return mixed
     * @see addData, setData
     */
    public function getData($name)
    {
        if (! array_key_exists($name, $this->data)) {
            return null;
        }
        return $this->data[$name];
    }

    /**
     * Clears the named data array
     *
     * @param name string
     * @return void
     */
    public function clearData($name)
    {
        if (array_key_exists($name, $this->data)) {
            unset($this->data[$name]);
        }
    }

    /**
     * Renders this page
     *
     * All buffers are finalized using processBuffers directly before calling
     * onRender
     *
     * dispatches two callbacks: prerender and postrender
     *
     * @see processBuffers
     * @return void
     */
    final public function render()
    {
        $this->dispatchCallback('prerender', $this);

        if (isset($this->component)) {
            $this->component->render();
        }
        $this->processBuffers();

        $output = $this->onRender();
        $this->headers->send();
        print $output;

        $this->dispatchCallback('postrender', $this);
    }

    /**
     * Generates page output
     *
     * The default implementation attempts to load the $template property
     * through the TemplateSystem and render through it, or if the $template
     * property is not set, simply returns the 'content' buffer
     *
     * @return string
     * @see $template, getTemplateArguments
     */
    protected function onRender()
    {
        if (isset($this->template)) {
            $template = TemplateSystem::load($this->template);
            return $template->render($this->getTemplateArguments());
        } else {
            return $this->getBuffer('content');
        }
    }

    /**
     * Returns an associative array contaning arguments with which to process
     * the page template, used by the default onRender implementation.
     *
     * The default implementation simply addes a 'page' entry for $this
     *
     * @return array
     */
    protected function getTemplateArguments()
    {
        $args = array('page' => $this);

        // Compatability with old sites
        if (is_a($this, 'LayoutDescription')) {
            $args['layout'] = $this;
        }

        return $args;
    }
}

?>
