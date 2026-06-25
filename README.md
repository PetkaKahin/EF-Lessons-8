# Домашка 8

## Первый запуск (инициализация)
```bash
make init
```

## Запустить и выключить приложение

Выполнять после инициализации

Запуск:
```bash
make up
```

Отключить:
```bash
make down
```

## Демонстрация webhooks

Если данных нет, то заполнить тестовыми:
```bash
make seed
```

Запустить демонстрацию:
```bash
make artisan webhook:demo
```
После чего можно смотреть в таблицу webhook_attempts

## Тесты
```bash
make test
```

---

#### Проверка состояния приложения: http://localhost:8080/health
#### Проверка полного состояния приложения(БД и Redis): http://localhost:8080/ready
#### Метрики: http://localhost:8080/metrics

