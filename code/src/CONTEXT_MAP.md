# Context Map

* OrderContext and PaymentContext взаимодействуют через event-driven архитектуру.
* OrderContext publicate domain event `OrderCreatedEvent`.
* PaymentContext listen `OrderCreatedEvent` и создает запись об ожидающем платеже.
* Каждому контексту соответствует отдельный API и инфраструктура хранения данных.