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
Gateway-->>User: response
deactivate Gateway
Message Broker-->>Billing Service: consume
activate Billing Service
Billing Service ->> Billing Service: creating account
deactivate Billing Service
Message Broker-->>Notification Service: consume
deactivate Message Broker
activate Notification Service
Notification Service ->> Notification Service: caching user
deactivate Notification Service

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
Note right of Message Broker: PaymentRequested
Message Broker-->>Billing Service: consume
deactivate Message Broker
activate Billing Service
alt Success
Billing Service->>Message Broker: publish
activate Message Broker
Note right of Message Broker: PaymentSuccessful
else Failure
Billing Service->>Message Broker: publish
end
deactivate Billing Service
Note right of Message Broker: PaymentFailed
Message Broker-->>Order Service: consume
deactivate Message Broker
activate Order Service
alt Success
Order Service->>Message Broker: publish
activate Message Broker
Note right of Message Broker: OrderPayed
else Failure
Order Service->>Message Broker: publish
end
deactivate Order Service
Note right of Message Broker: OrderFailed
Message Broker-->>Notification Service: consume
deactivate Message Broker
activate Notification Service
Notification Service ->> Notification Service: sending email
deactivate Notification Service

