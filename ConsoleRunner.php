<?php
/**
 * @changelog 18 Jan 2018 Added compatibility with Windows
 * @changelog 18 Jan 2018 Returned compatibility with PHP-FPM
 */

namespace cbackup\console;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;


/**
 * ConsoleRunner - a component for running console commands on background.
 *
 * Usage:
 * ```
 * ...
 * $cr = new ConsoleRunner(['file' => 'my/path/to/yii']);
 * $cr->run('controller/action param1 param2 ...');
 * ...
 * ```
 * or use it like an application component:
 * ```
 * // config.php
 * ...
 * components [
 *     'consoleRunner' => [
 *         'class' => 'vova07\console\ConsoleRunner',
 *         'file'  => 'my/path/to/yii' // or an absolute path to console file
 *     ]
 * ]
 * ...
 *
 * // some-file.php
 * Yii::$app->consoleRunner->run('controller/action param1 param2 ...');
 * ```
 */
class ConsoleRunner extends Component
{

    /**
     * Console application file that will be executed.
     * Usually it can be `yii` file.
     *
     * @var string
     */
    public $file;

    /**
     * Absolute path to PHP executable
     * @var string
     */
    private $php;

    /**
     * @inheritdoc
     */
    public function init()
    {

        parent::init();

        if ($this->file === null) {
            throw new InvalidConfigException('The "file" property must be set.');
        }

        if($this->isWindows()) {

            $this->php = exec('where php.exe');

            if( is_dir(PHP_BINDIR) && file_exists(PHP_BINDIR.DIRECTORY_SEPARATOR.'php.exe') ) {
                $this->php = PHP_BINDIR.DIRECTORY_SEPARATOR.'php.exe';
            }

            if( empty($this->php) ) {
                throw new NotSupportedException('Could not find php.exe');
            }

        }
        else {
            $this->php = PHP_BINDIR . '/php';
        }

    }

    /**
     * Running console command on background
     *
     * @param string $cmd Argument that will be passed to console application
     * @return boolean
     */
    public function run($cmd)
    {

        $cmd = $this->php . ' ' . Yii::getAlias($this->file) . ' ' . $cmd;

        if ($this->isWindows() === true) {
            pclose(popen('start /b ' . $cmd, 'r'));
        }
        else {
            pclose(popen($cmd . ' > /dev/null 2>&1 &', 'r'));
        }

        return true;

    }

    /**
     * Check if operating system is Windows
     *
     * @return boolean true if it's Windows OS
     */
    protected function isWindows()
    {
        if( mb_stripos(PHP_OS, 'WIN') !== false ) {
            return true;
        }
        else {
            return false;
        }
    }

}
