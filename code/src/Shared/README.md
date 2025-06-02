# Shared компоненты приложения

Этот каталог содержит общие компоненты, которые используются во всех контекстах приложения.

## Rate Limiting

Механизм ограничения частоты запросов для защиты API от перегрузки.

### Архитектура

```
Shared/
├── Infrastructure/
│   └── RateLimiting/
│       ├── Storage/           # Хранение данных о лимитах
│       ├── Strategy/          # Стратегии ограничения
│       ├── Identifier/        # Идентификация клиентов
│       ├── Configuration/     # Конфигурация лимитов
│       ├── Exception/         # Исключения
│       └── EventListener/     # Интеграция с Symfony
└── Presentation/
    └── Http/
        └── Attribute/         # PHP атрибуты для декларативного использования
```

### Как это работает

1. **Определение лимитов**: Лимиты задаются декларативно через PHP атрибут `RateLimit`.
2. **Идентификация запросов**: Каждый запрос идентифицируется по IP-адресу клиента (по умолчанию).
3. **Подсчет запросов**: Redis хранит счетчики запросов в ключах с автоматическим истечением срока действия.
4. **Проверка лимитов**: EventListener перехватывает запросы и проверяет, не превышен ли лимит.
5. **Обработка превышения**: При превышении лимита возвращается статус 429 и заголовки с информацией.

### Использование

Добавить ограничение частоты запросов к контроллеру или методу:

```php
// Ограничить на уровне метода
#[Route('/api/payments/create', methods: ['POST'])]
#[RateLimit(limit: 100, windowSizeSeconds: 60)] // 100 запросов в минуту
public function createPayment(Request $request): Response
{
    // ...
}

// Или ограничить на уровне класса для всех методов
#[RateLimit(limit: 1000, windowSizeSeconds: 3600)] // 1000 запросов в час
final class PaymentController
{
    // ...
}
```

### Ключевые особенности

- **Гибкость**: Разные лимиты для разных эндпоинтов
- **Масштабируемость**: Redis обеспечивает высокую производительность и работу в кластере
- **Расширяемость**: Легко добавить новые стратегии и способы идентификации
- **Стандартизация**: Используются стандартные HTTP-заголовки для клиентов

### Возвращаемый ответ при превышении лимита

```json
{
    "status": "error",
    "code": 101,
    "message": "Rate limit exceeded for resource 'api_route:create_payment'. Limit: 100. Wait 35 seconds before retrying.",
    "wait_seconds": 35
}
```

С HTTP-заголовками:
- `X-RateLimit-Limit: 100`
- `X-RateLimit-Remaining: 0`
- `X-RateLimit-Reset: 1717133875` (Unix timestamp)
- `Retry-After: 35` (секунды)

### Конфигурация

Конфигурация сервисов находится в файле `src/Shared/Resources/config/rate_limiting.yaml`.

---

## Outbox Pattern

Механизм надежной доставки доменных событий через асинхронные сообщения.

#### Архитектура

- **OutboxEventProcessor**: Обрабатывает неотправленные события из хранилища
- **OutboxMessageEnvelope**: Обертка для сообщений с метаданными
- **OutboxMessageEnvelopeHandler**: Преобразует сообщения обратно в доменные события

#### Использование

Доменные события публикуются через абстрактный репозиторий:

```php
// Изменения в домене автоматически регистрируют события
$aggregate->doSomething();

// Сохранение агрегата через репозиторий публикует события
$this->repository->save($aggregate);
```

Обработка событий запускается командой:

```bash
bin/console app:process-outbox
```

### CQRS

Разделение операций чтения и записи через шаблон Command Query Responsibility Segregation.

#### Архитектура

- **CommandBus**: Шина команд для операций изменения состояния
- **QueryBus**: Шина запросов для операций чтения
- **ApplicationService**: Централизованный интерфейс для доступа к обеим шинам

#### Использование

```php
// Выполнение команды (изменение без возврата результата)
$this->applicationService->command(new CreatePaymentCommand($amount, $description));

// Выполнение запроса (получение данных)
$payment = $this->applicationService->query(new GetPaymentQuery($paymentId));
```

### Спецификации

Комбинируемые спецификации для выражения бизнес-правил в виде составных логических выражений.

#### Доступные операторы

- **AndSpecification**: Логическое И (&&)
- **OrSpecification**: Логическое ИЛИ (||)
- **NotSpecification**: Логическое НЕ (!)
- **AndNotSpecification**: Логическое И-НЕ (&& !)
- **OrNotSpecification**: Логическое ИЛИ-НЕ (|| !)

#### Использование

```php
$spec = new AndSpecification(
    new CustomerIsActiveSpecification(),
    new OrNotSpecification(
        new OrderHasItemsSpecification(),
        new OrderIsOverDueSpecification()
    )
);

if ($spec->isSatisfiedBy($customer)) {
    // Действие, если спецификация выполняется
}