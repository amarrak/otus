sequenceDiagram

participant User
participant User Service
participant Order Service
participant Billing Service
participant Notification Service

User->>User Service: POST /users/register
activate User Service
User Service->>Billing Service: POST /accounts/create {userId}
activate Billing Service
Billing Service-->>User Service: 201 CREATED {accountId}
deactivate Billing Service
User Service-->>User: 201 CREATED
deactivate User Service

User->>Order Service: POST /orders/create
activate Order Service
Order Service->>Billing Service: POST /accounts/withdraw {userId, sum}
activate Billing Service
Billing Service-->>Order Service: 200 OK
deactivate Billing Service
Order Service->>Notification Service: POST /send_email {template_id, email, context}
activate Notification Service
Notification Service -->> Order Service: 202 ACCEPTED
Order Service-->>User: 201 CREATED
deactivate Order Service
Notification Service ->> Notification Service: sending email
deactivate Notification Service