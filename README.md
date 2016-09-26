# mysql-timeout
Customizing the timeout for a database query

## Install

composer require wwaayyaa/mysql-timeout

## Example

php code

```php
    $conf = Conf::$db1;
    print_r($conf);
    $d = new MysqlTimeout($conf);
    $r = $d->query('select sleep(2) as `sleep`,2 as `sec`;');
    print_r($r);
    $r = $d->query('select * from dtk_zhibo_chat_log where zhiboId = 1292 limit 1;');
    print_r($r);
    try{
    $r = $d->query('select sleep(5) as `sleep`,5 as `sec`;');
    }catch(Exception $e){
        echo sprintf("error message:%s ,error code : %d \n",$e->getMessage(),$e->getCode());
    }
    $r = $d->query('select sleep(5) as `sleep`,5 as `sec`;',6);
    print_r($r);
    $r = $d->update("update dtk_zhibo_chat_log set addtime=now() where id = 6 or id = 17;");
    echo sprintf("success rows:%d \n",$r);
    $r = $d->insert("insert into dtk_zhibo_chat_log (zhiboId,content) values (1292,'test');");
    echo sprintf("primary id:%d \n",$r);
```
result

```php
Array
(
    [host] => 127.0.0.1
    [port] => 3306
    [user] => root
    [password] => root
    [dbname] => test
    [charset] => utf8
)
Array
(
    [0] => Array
        (
            [sleep] => 0
            [sec] => 2
        )

)
Array
(
    [0] => Array
        (
            [id] => 6
            [zhiboId] => 1292
            [content] => hello,young.
            [addtime] => 2016-09-26 11:12:00
        )

)
error message:timeout ,error code : 922922
Array
(
    [0] => Array
        (
            [sleep] => 0
            [sec] => 5
        )

)
success rows:2
primary id:1096

```

## Method

```php
	/**
     * @return mixed  query data
     */
	public function query($sql, $timeout = 3);

	/**
     * @return int   rows count
     */
	public function update($sql, $timeout = 3);

	/**
     * @return int   data primary id
     */
	public function insert($sql, $timeout = 3);

	/**
     * @return int   rows count
     */
	public function delete($sql, $timeout = 3);

```

## Args

### timeout (defalut : 3)
 - The MsyqlTimeout inspection cycle is 0.05 seconds,
 - so the minimum value of the $timeout parameter is 0.05,
 - the maximum value depends on the PHP.ini max_execution_time and MySQL timeout settings.
