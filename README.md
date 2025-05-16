# 勤怠管理アプリ

## 環境構築

### 初期構築
```
docker compose up -d --build
docker compose exec php composer install
docker compose exec php cp .env.example .env
docker compose exec php php artisan key:generate
docker compose exec php php artisan storage:link
docker compose exec php chmod -R 777 storage bootstrap/cache
```

.envで以下のように書き換える
```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

### マイグレーション
```
docker compose exec php php artisan migrate:fresh --seed
```

### 停止
```
docker compose down --remove-orphans
```

### 起動
```
docker compose up -d
```

### キャッシュクリア
```
docker compose exec php php artisan cache:clear
```

### 設定キャッシュ
```
docker compose exec php php artisan config:cache 
docker compose exec php php artisan config:cache
```

### テストを実行
```
docker compose exec php php artisan test
```

## 使用技術(実行環境)
- PHP 7.4.9
- Laravel 8.83.8
- MySQL 10.3.39

## ER 図 

<img width="484" alt="Image" src="https://github.com/user-attachments/assets/5c84924a-704c-4d09-92a4-a220d2d08984" />

- ホーム画面 http://localhost/
- Adminログイン http://localhost/admin/login
- phpMyAdmin : http://localhost:8080/
