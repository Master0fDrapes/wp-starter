<?php

/**
 * option descriptor for select, radio, multiple checkbox ...
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * this class describes the options for select, radio and multiple checboxes
 */
class DUPX_Param_item_form_option
{
    const OPT_ENABLED  = 'enabled';
    const OPT_DISABLED = 'disabled';
    const OPT_HIDDEN   = 'hidden';

    public $value        = '';
    public $label        = '';
    public $attrs        = array();
    protected $optStatus = self::OPT_ENABLED;
    protected $note      = '';

    /**
     *
     * @param mixed $value // option value
     * @param string $label     // label
     * @param string|function $optStatus // option status. can be a fixed status or a callback
     * @param array $attrs // option attributes
     */
    public function __construct($value, $label, $optStatus = self::OPT_ENABLED, $attrs = array())
    {
        $this->value     = $value;
        $this->label     = $label;
        $this->optStatus = $optStatus;
        $this->attrs     = (array) $attrs;
    }

    /**
     * get current statis.
     * @return string
     */
    public function getStatus()
    {
        if (is_callable($this->optStatus)) {
            return call_user_func($this->optStatus, $this);
        } else {
            return $this->optStatus;
        }
    }

    /**
     *
     * @param string|function $optStatus // option status. can be a fixed status or a callback
     */
    public function setStatus($optStatus)
    {
        $this->optStatus = $optStatus;
    }

    /**
     *
     * @param string|callable $note
     */
    public function setNote($note)
    {
        $this->note = is_callable($note) ? $note : ((string) $note);
    }

    /**
     *
     * @return string
     */
    public function getNote()
    {
        $note = '';
        if (is_callable($this->note)) {
            $note = call_user_func($this->note, $this);
        } else {
            $note = $this->note;
        }

        return (empty($note) ? '' : '<div class="sub-note" >' . $note . '</div>');
    }

    /**
     *
     * @return bool
     */
    public function isEnable()
    {
        return $this->getStatus() == self::OPT_ENABLED;
    }

    /**
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->getStatus() == self::OPT_DISABLED;
    }

    /**
     *
     * @return bool
     */
    public function isHidden()
    {
        return $this->getStatus() == self::OPT_HIDDEN;
    }
}
