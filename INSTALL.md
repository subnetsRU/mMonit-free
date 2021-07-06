#INSTALL
---------

1. Скопируйте файлы проекта в директорию вашего HTTP сервера.
```
# cd /usr/local/www
# git clone https://github.com/subnetsRU/mMonit-free.git
```

2. Установите права на запись для папки collector/data для пользователя, от которого запущен HTTP сервер.
```
# chown www:www mMonit-free/collector/data
```

3. Отредактируйте файл config.php

4. В конфигурации monit (https://mmonit.com/monit/documentation/monit.html#MANAGE-YOUR-MONIT-INSTANCES) укажите путь для отправки данных:

`set mmonit http://ip-or-hostname-of-the-web-server/mMonit-free/collector/index.php`
Например:
```
set mmonit http://192.168.1.1/mMonit-free/collector/index.php
    with timeout 15 seconds 
```

5. Запускайте демон monit и пользуйтесь.
Для того, чтобы видеть процесс отладки запускайте демон с ключём -v:
```
# monit -v
```
Или -l logfile _(Print log information to this file)_:

```
monit -v -l /tmp/mmonit_verbose.log
```
