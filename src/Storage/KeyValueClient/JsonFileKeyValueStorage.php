<?php
declare(strict_types=1);

namespace App\Storage\KeyValueClient;

use App\Storage\Exception\BadExclusiveLockException;
use App\Storage\Exception\CannotCreateStorageDirectoryException;
use App\Storage\Exception\KeyNotFoundException;
use App\Storage\Exception\StorageFileNotFoundException;
use App\Storage\KeyValueInterface;


/**
 * Class JsonFileKeyValueStorage
 *
 * Simple file based storage. Dont use it for distributed systems or big storages. for test task only -)
 *
 */
class JsonFileKeyValueStorage implements KeyValueInterface
{
    private   $filename;
    private   $prefix;
    private   $config;
    protected $defaults = [
        'attempts' => 2,
        'sleep'    => 10,
    ];

    /**
     * JsonFileKeyValueStorage constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->filename = $config['filename'];
        $this->prefix = $config['prefix'];
        $this->config = $config + $this->defaults;
    }

    /**
     * @param string $key
     * @return array|string
     * @throws KeyNotFoundException
     * @throws StorageFileNotFoundException
     */
    public function get(string $key)
    {
        $result = $this->mget([$key]);
        if (\count($result) === 0 || !isset($result[$key])) {
            throw new KeyNotFoundException($key);
        }

        return $result[$key];
    }


    /**
     * @param array $keys
     * @return array
     * @throws StorageFileNotFoundException
     */
    public function mget(array $keys): array
    {
        $contents = $this->read();
        $intersection = \array_intersect_key(
            $contents,
            \array_flip(\array_map([$this, 'inStorageKey'], $keys))
        );

        return \array_combine(
            \array_map([$this, 'originalKey'], \array_keys($intersection)),
            \array_values($intersection)
        );
    }

    /**
     * @param string $key
     * @param $value
     * @return bool
     * @throws BadExclusiveLockException
     * @throws StorageFileNotFoundException
     */
    public function put(string $key, $value): bool
    {
        return $this->mput([$key => $value]);
    }

    /**
     * @param array $values
     * @return bool
     * @throws BadExclusiveLockException
     * @throws StorageFileNotFoundException
     */
    public function mput($values): bool
    {
        $input = [];
        foreach ($values as $key => $value) {
            $input[$this->inStorageKey(strval($key))] = $value;
        }
        if (!\file_exists($this->filename)) {
            return $this->write($input);
        }

        return $this->write($input + $this->read());
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function delete(string $key)
    {
        return $this->mdelete([$key]);
    }

    /**
     * @param array $keys
     * @return bool|mixed
     * @throws BadExclusiveLockException
     * @throws StorageFileNotFoundException
     */
    public function mdelete(array $keys):bool
    {
        $content = $this->read();
        $keysToDelete = \array_map([$this, 'inStorageKey'], $keys);
        $preNewData = \array_diff_key($content, \array_flip($keysToDelete));
        $newData = $this->regexDelete($keysToDelete, $preNewData);

        return $this->write($newData);
    }

    /**
     * NOTE: only keys alike key* (* in the end) will be deleted, will delete key...* if one exists
     * @param $keys
     * @param $data
     * @return array
     */
    protected function regexDelete($keys, $data): array
    {
        $regexKeys = \array_filter($keys, function ($key) {
            return \strpos($key, '*') === \strlen($key) - 1;
        });
        if (\count($regexKeys) === 0) {
            return $data;
        }
        $keysToDelete = [];
        foreach (\array_keys($data) as $key) {
            foreach ($regexKeys as $regexKey) {
                if (\substr($key, 0, \strlen($regexKey) -1) . '*' === $regexKey) {
                    $keysToDelete[] = $key;
                    break;
                }
            }
        }

        return \array_diff_key($data, \array_flip($keysToDelete));
    }

    /**
     * @param string $key
     * @return string
     */
    protected function inStorageKey(string $key): string
    {
        return $this->prefix . $key;
    }

    /**
     * @param string $inStorageKey
     * @return string
     */
    protected function originalKey(string $inStorageKey): string
    {
        return \substr($inStorageKey, \strlen($this->prefix));
    }

    /**
     * @return array
     * @throws StorageFileNotFoundException
     */
    protected function read(): array
    {
        if (!file_exists($this->filename)) {
            throw new StorageFileNotFoundException($this->filename);
        }

        return \json_decode(\file_get_contents($this->filename), true);
    }

    /**
     * @param $data
     * @return bool
     * @throws BadExclusiveLockException
     */
    protected function write($data): bool
    {
        $attempts = $this->config['attempts'];
        $sleep = $this->config['sleep'];
        $exception = null;
        while ($attempts-- > 0) {
            try {
                return $this->writeExclusive($data);
            } catch (BadExclusiveLockException $e) {
                $exception = $e;
                sleep($sleep);
            }
        }

        throw $exception;
    }

    /**
     * NOTE: its bad locks try to use good lock based on redis or kinda, works only if only one service use this storage
     * @param int $time seconds
     * @throws BadExclusiveLockException
     */
    protected function createLock(int $time): void
    {
        $this->releaseLock();
        if (!\file_exists(\dirname($this->filename))) {
            if(true !== \mkdir(\dirname($this->filename), 0644, true)){
                throw new CannotCreateStorageDirectoryException(\dirname($this->filename));
            };
        }
        $locFileHandler = \fopen($this->filename . '.lock', 'x');
        if (!$locFileHandler) {
            throw new BadExclusiveLockException('Can\'t create lock file: "' . $this->filename . '.lock"');
        }
        \fwrite($locFileHandler, \json_encode(['pid' => \getmypid(), 'time' => \time() + $time]));
        \fclose($locFileHandler);
    }

    protected function releaseLock(): void
    {
        $lockName = $this->filename . '.lock';
        if (!\file_exists($lockName)) {
            return;
        }
        $lock = \json_decode(\file_get_contents($lockName), true);
        if (\getmypid() === $lock['pid'] || \time() >= $lock['time']) {
            \unlink($lockName);
        }
    }

    /**
     * @param $data
     * @return bool
     * @throws BadExclusiveLockException
     */
    protected function writeExclusive($data): bool
    {
        $this->createLock(120);
        try {
            $result = file_put_contents($this->filename, json_encode($data));
        }
        finally {
            $this->releaseLock();
        }

        return (bool)$result;
    }


}