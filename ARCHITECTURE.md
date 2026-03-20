# Cm_Cache_Backend_Redis Architecture

## Purpose

A Redis-backed cache backend for Zend_Cache, replacing file-based caching in
Magento 1 and Zend Framework applications.  Supports tag-based invalidation,
LZF compression, and both standalone and Sentinel Redis topologies.

## Directory Structure

```
Cm/Cache/Backend/
  Redis.php           — the single class (implements Zend_Cache_Backend_ExtendedInterface)
stats.php             — standalone Redis stats viewer script
tests/
  CommonBackendTest.php           — shared Zend_Cache backend test contract
  CommonExtendedBackendTest.php   — extended backend test contract
  RedisBackendStandaloneTest.php  — PHPUnit tests for standalone Redis
  RedisBackendAutoExpiryTest.php  — tests for auto-expiry behaviour
  RedisBackendTest.php            — main test suite
```

## Key Design Decisions

- **Tag-based invalidation via Redis sets**: each cache tag is stored as a Redis
  SET containing the IDs of all entries tagged with it; `clean(CLEANING_MODE_MATCHING_TAG)`
  pipelines `DEL` calls for all members of those sets in a single round-trip.
- **LZF compression**: optional transparent LZF compression reduces Redis memory
  use for large serialised values.
- **Lua scripting**: atomic operations (tag cleanup, conditional expiry) use Lua
  scripts executed server-side to avoid race conditions without requiring
  `MULTI/EXEC` transactions.
- **Sentinel support**: the `read_master` and `sentinel_servers` options allow
  connecting through Redis Sentinel for high-availability deployments.
- **Auto-expiry**: entries can be configured to use Redis native `EXPIRE` instead
  of tracking expiry in PHP, reducing overhead for simple TTL-only workloads.

## Extension Points

- Pass `compression_lib` option to choose between `lzf`, `snappy`, or `gzip`.
- Configure `sentinel_servers` + `sentinel_master` for HA deployments.
- Set `read_master = false` to allow reads from replica nodes.

## Dependency Flow

```
Consumer (Zend_Cache_Backend_ExtendedInterface)
  └── Cm_Cache_Backend_Redis
        └── PhpRedis extension (ext/redis) or Credis library
              └── Redis server (standalone or Sentinel cluster)
```
