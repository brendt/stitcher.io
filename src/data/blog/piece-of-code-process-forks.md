```php
function async(Process $process) : Process {
    socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $sockets);
    [$parentSocket, $childSocket] = $sockets;

    if (($pid = pcntl_fork()) == 0) {
        socket_close($childSocket);
        socket_write($parentSocket, serialize($process->execute()));
        socket_close($parentSocket);
        exit;
    }

    socket_close($parentSocket);

    return $process
        ->setStartTime(time())
        ->setPid($pid)
        ->setSocket($childSocket);
}

function wait(array $processes) : array {
    $output = [];

    while (count($processes)) {
        foreach ($processes as $key => $process) {
            $processStatus = pcntl_waitpid($process->getPid(), $status, WNOHANG | WUNTRACED);

            if ($processStatus == $process->getPid()) {
                $output[] = unserialize(socket_read($process->getSocket(), 4096));
                socket_close($process->getSocket());
                $process->triggerSuccess();

                unset($processes[$key]);
            } else if ($processStatus == 0) {
                if ($process->getStartTime() + $process->getMaxRunTime() < time() || pcntl_wifstopped($status)) {
                    if (!posix_kill($process->getPid(), SIGKILL)) {
                        throw new \Exception("Failed to kill {$process->getPid()}: " . posix_strerror(posix_get_last_error()));
                    }
                    
                    unset($processes[$key]);
                }
            } else {
                throw new \Exception("Could not reliably manage process {$process->getPid()}");
            }
        }
        
        if (!count($processes)) {
            break;
        }

        usleep(100000);
    }

    return $output;
}
```

The `Process` class, used to pass data in a defined way.

```php
abstract class Process
{
    protected $pid;
    protected $name;
    protected $socket;
    protected $successCallback;
    protected $startTime;
    protected $maxRunTime = 300;
    
    public abstract function execute();

    public function onSuccess(callable $callback) : Process {
        $this->successCallback = $callback;

        return $this;
    }

    public function triggerSuccess() {
        if (!$this->successCallback) {
            return null;
        }

        return call_user_func_array($this->successCallback, [$this]);
    }

    public function setPid($pid) : Process {
        $this->pid = $pid;

        return $this;
    }

    public function getPid() {
        return $this->pid;
    }

    public function setSocket($socket) : Process {
        $this->socket = $socket;

        return $this;
    }

    public function getSocket() {
        return $this->socket;
    }

    public function setName(string $name) : Process {
        $this->name = $name;

        return $this;
    }

    public function getName() : string {
        return $this->name;
    }

    public function setStartTime($startTime) {
        $this->startTime = $startTime;

        return $this;
    }

    public function getStartTime() {
        return $this->startTime;
    }

    public function setMaxRunTime(int $maxRunTime) : Process {
        $this->maxRunTime = $maxRunTime;

        return $this;
    }

    public function getMaxRunTime() : int {
        return $this->maxRunTime;
    }
}
```

A concrete Process implementation.

```php
class MyProcess extends Process
{
    public function execute() {
        sleep(1);
        
        return true;
    }
}
```

And bringing it all together.

```php
$processA = async(new MyProcess());
$processB = async(new MyProcess());

$output = wait([$processA, $processB]);

print_r($output);
die('Done!');
```
