<?php

namespace App\Console\Commands;

use App\Models\KpiLog;
use Illuminate\Console\Command;

/**
 * @property KpiLog KpiLog
 *
 * */
class BaseShell extends Command
{
    protected $signature    = 'command:name';
    protected $description  = 'Command description';
    protected $args         = null;
    private $__flushLimit   = 1024;

    public function __construct() {
        parent::__construct();
    }

    public function handle () {
        $function   = $this->argument('function');
        $this->args = $this->argument('args');
        $this->$function();
        return 0;
    }

    public function out ($output) {
        echo "$output \n";
    }

    const STT_EXCEPTION = 'Exception';
    const STT_FAILED    = 'Failed';
    const STT_RUNNING   = 'Running';
    const STT_SUCCESS   = 'Success';
    public $jobId       = null;

    protected function _initJob($action, $args) {
        $dataSave = [
            'hostname'  => gethostname(),
            'pid'       => posix_getpid(),
            'action'    => $action,
            'input'     => empty($args) ? '' : json_encode($args),
            'status'    => self::STT_RUNNING,
            'env'       => print_r($_SERVER, true),
            'stdout'    => '',
        ];

        $result = $this->KpiLog->saveOne($dataSave);
        if ($result) {
            $this->jobId = $result;
            return $this->jobId;
        }

        return false;
    }

    protected function _checkOtherRunning($job) {

        $file = '/tmp/' . $job;
        if (!file_exists($file)) {
            file_put_contents($file, posix_getpid());
            return;
        }

        $pid = (int) file_get_contents($file);
        if (posix_getpgid($pid) === false) {
            file_put_contents($file, posix_getpid());
        } else {
            throw new Exception("Error: another job is running with pid: $pid");
        }
    }

    protected function _clearRunning($job) {
        $file = '/tmp/' . $job;
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    protected function _appendOutput($output) {
        if (!empty($this->jobId)) {
            $this->KpiLog->updateById($this->jobId, [
                'stdout' => $output
            ]);
        }
    }

    protected function _saveOutput() {
        if (ob_get_length() > 0) {
            $output = ob_get_flush();
            $this->_appendOutput(mb_substr($output, max(mb_strlen($output) - $this->__flushLimit, 0)));
        }
    }

    public function hr($newlines = 0, $width = 63) {
        $this->out(str_repeat('-', $width));
    }

    public function runJob() {
        $this->KpiLog = getInstance('KpiLog');
        $function     = $this->args[0] ?? null;

        array_shift($this->args);

        if (empty($function)) {
            $this->out('Missing action for job shell');
            return;
        }

        $this->out("Job start: " . date('Y-m-d H:i:s'));
        $start = microtime(true);


        $job = get_class($this) . '::' . $function;

        $this->_initJob($job, $this->args);

        ob_start();
        try {
            $this->_checkOtherRunning($job);

            $result = $this->$function();
            if ($result === 0) {
                if (!empty($this->jobId)) {
                    $this->KpiLog->updateById($this->jobId, ['status' => self::STT_SUCCESS]);
                }
            } else {
                if (!empty($this->jobId)) {
                    $this->KpiLog->updateById($this->jobId, ['status' => self::STT_FAILED]);
                }
            }

            $this->_clearRunning($job);

        } catch (Throwable $ex) {
            $this->out("Exception: " . $ex->getMessage());
            if (!empty($this->jobId)) {
                $this->KpiLog->updateById($this->jobId, [
                    'status'    => self::STT_EXCEPTION,
                    'message'   => $ex->getMessage()
                ]);
            }

        } finally {
            $this->_saveOutput();
        }

        $this->hr();
        $this->out('Job done: ' . date('Y-m-d H:i:s'));
        $end = microtime(true);
        $this->out('Time execute: ' . ($end - $start));
    }


}
