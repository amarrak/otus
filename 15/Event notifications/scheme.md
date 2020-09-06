sequenceDiagram

participant User
participant Gateway
participant Message Broker
participant User Service
participant Order Service
participant Billing Service
participant Notification Service

User->>Gateway: POST /register
activate Gateway
Gateway->>Message Broker: publish
deactivate Gateway
activate Message Broker
Note left of Message Broker: RegistrationRequested
Message Broker-->>User Service: consume
deactivate Message Broker
activate User Service
User Service->>Message Broker: publish
deactivate User Service
activate Message Broker
Note right of Message Broker: UserCreated
Message Broker-->>Gateway: consume
activate Gateway
Gateway->>User Service: GET /users/{id}/
User Service-->>Gateway: user info
Gateway-->>User: response
deactivate Gateway
Message Broker->>Billing Service: consume
deactivate Message Broker
activate Billing Service
Billing Service->>User Service: GET /users/{id}/
User Service-->>Billing Service: user info
Billing Service ->> Billing Service: creating account
deactivate Billing Service

User->>Gateway: POST /order
activate Gateway
Gateway->>Message Broker: publish
deactivate Gateway
activate Message Broker
Note left of Message Broker: OrderRequested
Message Broker-->>Order Service: consume
deactivate Message Broker
activate Order Service
Order Service->>Message Broker: publish
activate Message Broker
Note right of Message Broker: OrderCreated
Message Broker-->>Gateway: consume
deactivate Message Broker
activate Gateway
Gateway-->>User: response
deactivate Gateway
Order Service->>Message Broker: publish
deactivate Order Service
activate Message Broker
Note right of Message Broker: BillingRequested
Message Broker-->>Billing Service: consume
deactivate Message Broker
activate Billing Service
Billing Service->>Order Service: GET /orders/{id}/
Order Service-->>Billing Service: order info
Billing Service->>Message Broker: publish
deactivate Billing Service
activate Message Broker
Note right of Message Broker: BillPayed
Message Broker-->>Order Service: consume
deactivate Message Broker
activate Order Service
Order Service->>Message Broker: publish
deactivate Order Service
activate Message Broker
Note right of Message Broker: OrderPayed
Message Broker-->>Notification Service: consume
deactivate Message Broker
activate Notification Service
Notification Service->>Order Service: GET /orders/{id}/
Order Service-->>Notification Service: order info
Notification Service ->> Notification Service: sending email
deactivate Notification Service

