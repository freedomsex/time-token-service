# Timer-Token service

Простой сервис для сохранения и получения времени готовности токена к проверке. В качестве токена может использоваться любой идентификатор(ID). Используется для установки времени ожидания доступа к сервису как превентивная защита от DDoS.

## Принцип работы

Сохраняет в установленный экземпляр кэша идентификатор в качестве `ключа` и значение времени ожидания в качестве `значения`. Использует PSR-6 совместимый экземпляр кэша. 

## Как использовать

```php

use FreedomSex\Services\TimeTokenService;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

// любой PSR-6 совместимый экземпляр кэша 
// для примера указан FilesystemAdapter Symfony
// обычно используется MemcachedAdapter
$memory = new FilesystemAdapter(); 
$timeTokenService = new TimeTokenService($memory);

$id = '123456'; // любая строка 

$timeTokenService->start($id, 2);
$timeTokenService->ready($id); // FALSE
sleep(2); // Подождать
$timeTokenService->ready($id); // TRUE
```

### start($id, $delay = null, $expire = null)

По умолчанию время ожидания `TimeTokenService::DEFAULT_DELAY` 2 секунды. Время хранения токена ожидания `TimeTokenService::TOKEN_EXPIRE` 10 секунд. 

```php
// Параметры $delay и $expire не обязательны
$timeTokenService = new TimeTokenService($memory, $delay = null, $expire = null); 
// OR
$timeTokenService->setDelay($time);
$timeTokenService->setExpire($time);
// OR
$timeTokenService->start($id, $delay = null, $expire = null);
// Параметры $delay и $expire не обязательны
```

### ready($id)

Возвращает NULL если `$id` не существует или истекло время хранения. TRUE - если токен готов, FALSE - время не пришло.


### left($id)

Сколько осталось времени. Отрицательное значение - токен готов, прошло времени от наступления готовности. Нулевое - токен готов только что.

### restore($id, $delay = null)

Получить ожидаемое время готовности токена `$delay` по `$id` установленное на старте. Если не существует - вернет дефолтное значение или `$delay`.

## Другие функции

### expect($id)

Вернет ожидаемое время готовности токена `$delay` по `$id` или NULL

### delay($time = null)

Вернет время ожидания `$time` или дефолтное значение

### expires($expect, $expire = null)

Время хранения токена после которого он станен недоступен. 
