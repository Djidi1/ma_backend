# Установка:
1. Переходим в корневую директорию сайта. Директория должна быть пустая.
2. Запускаем команду (включая точку в конце) `git clone https://github.com/Djidi1/ma_backend.git .`
3. Запускаем команду `npm install`
4. Запускаем команду `composer install`
5. Копируем файл `.env` в нем указываем:
	- новый адрес сайта `APP_URL` и имя БД `DB_DATABASE`
	- настройки для сервера отправки почты: 
		`MAIL_HOST=192.168.7.33`
		`MAIL_PORT=25`
6. Запускаем команду `php artisan migrate`
7. Запускаем команду `php artisan passport:install --force`
8. Регистрируемся на сайте
9. В базе данных присваиваем себе роль администратора `UPDATE [dbo].[users] SET role_id = 1`

# Обновление:
1. Переходим в директорию сайта
2. Запускаем команду git pull
3. Запускаем команду php artisan migrate

-------
## TimeWeb

Для запуска скрипотв из консоли TimeWeb:
    `alias php='/opt/php71/bin/php'`
 
Для получения изменений из Гита:
    `git pull origin master`
    
Для запуска сервера:
    `npm run watch`
    
Если есть проблемы с памятью при запуске в консоли:
    `php -d memory_limit=-1 composer.phar require laravel/passport`        
	

Для установки на хостинге	:
http://timeweb.com/ru/help//pages/viewpage.action?pageId=8781927
тзь кгт 
alias node='/home/c/cx39083/nodejs/bin/node'
alias npm='/home/c/cx39083/nodejs/bin/npm'
export PATH=$PATH:/home/c/cx39083/nodejs/bin/

## Ошибки
1. Ошибка преобразования времени исправляется в файле `/web/ma_backend/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Concerns/HasAttributes.php` строка `748` удаляем `.u` из `str_replace('.v', '', $this->getDateFormat())`
