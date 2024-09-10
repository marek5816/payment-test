# Payment API test

## Overview
This repository contains a Symfony application for the Metricalo test assignment

## Requirements

Before you can run this project, you'll need to have Docker installed.

### Installing Docker

1. **For Windows and macOS:**
   - Visit the [Docker website](https://www.docker.com/get-started) and download the appropriate installer for your operating system.
   - Follow the installation guide provided by Docker to complete the setup.

2. **For Linux:**
   - You can install Docker using your package manager. For most Linux distributions, you can use the following commands:
     ```bash
     sudo apt-get update
     sudo apt-get install docker-ce docker-ce-cli containerd.io
     ```
   - Ensure that Docker is enabled and running by using:
     ```bash
     sudo systemctl enable docker
     sudo systemctl start docker
     ```

After installing Docker, verify the installation by running `docker --version` in your terminal.

## Setup project with docker
Navigate to the project directory, then build and run the containers with ``docker compose up --build``.

The web server will then run under port ``8085``

## Running PHPUnit tests
1. Open shell for php container - ``docker exec -it <container_name> /bin/bash``
2. Run PHPunit tests - ``php bin/phpunit``

## API Endpoint

- **POST /payment/request/{aci|shift4}**
  - **Description**: Process a payment through the specified gateway.
  - **URL Parameters**:
    - `aci|shift4`: The payment gateway. Choose between `aci` and `shift4`.
  - **Body Parameters** (raw `application/json`, required):
    - `amount` (float, required): The amount of the transaction.
    - `currency` (string, required): The currency in which the transaction will be processed in ISO 4217  (e.g., "USD", "EUR").
    - `cardNumber` (string, required): The credit card number for the transaction.
    - `cardExpYear` (string, required): The expiration year of the credit card.
    - `cardExpMonth` (string, required): The expiration month of the credit card.
    - `cardCVV` (string, required): The CVV code of the credit card.

  - **Success Response Example** (Code and message are included if provided by the gateway):
    ```json
    {
        "info": "Payment processed successfully.",
        "code": "000.100.110",
        "message": "Request successfully processed in 'Merchant in Integrator Test Mode'",
        "id": "8ac7a4a291c70b5b0191c712e1fd0865",
        "time": "1725621789",
        "amount": 100,
        "currency": "EUR",
        "cardBin": "401200"
    }
    ```

  - **Error Response Example** (Code and message are included if provided by the gateway. For API level, only the message is provided.):
    ```json
    {
        "error": {
            "info": "Error processing input parameters",
            "code": "",
            "message": "Error in 'cardNumber': The card number must be in range of 8-19 digits."
        }
    }
    ```

## Command
- **payment:request**
  - **Description**: Process a payment through the specified gateway using the command line.
  - **Usage**:
    ```bash
    php bin/console payment:request [gateway] --amount=[amount] --currency=[currency] --card_number=[card_number] --card_exp_year=[card_exp_year] --card_exp_month=[card_exp_month] --card_cvv=[card_cvv]
    ```

  - **Arguments**:
    - `gateway` (string, required): The payment gateway to process the payment (e.g., 'aci' or 'shift4').
  - **Options**:
    - `--amount` (float, required): The amount of the transaction.
    - `--currency` (string, required): The currency in which the transaction will be processed in ISO 4217 (e.g., "USD", "EUR").
    - `--card_number` (string, required): The credit card number for the transaction.
    - `--card_exp_year` (string, required): The expiration year of the credit card.
    - `--card_exp_month` (string, required): The expiration month of the credit card.
    - `--card_cvv` (string, required): The CVV code of the credit card.
  - **Success Output Example** (Code and message are included if provided by the gateway):
    ```bash
    [OK] Payment processed successfully.
    [INFO] Code: 000.100.110
    [INFO] Message: Request successfully processed in 'Merchant in Integrator Test Mode'    
    [INFO] Id: char_ytJ0OQH7O8yh73Ox6hFUisnS
    [INFO] Time: 1725623209
    [INFO] Amount: 150
    [INFO] Currency: EUR
    [INFO] Card Bin: 411111
    ```
  - **Error Output Example** (Code and message are included if provided by the gateway. For API level, only the message is provided.):
    ```bash
    [ERROR] Error processing input parameters.                                                                             
    [ERROR] Code:                                                                                                          
    [ERROR] Message: Error in 'cardNumber': The card number must be in range of 8-19 digits. 
    ```

## To Do (Added on 10.9.2024 after submission)
The application can be improved in several ways to enhance its functionality
- Implement rate limiting
- Add authentication using MID and HMAC
- Fix naming for gateway services (add a prefix)
- Add logging for all API requests and responses
- Implement real-time monitoring/alerting for critical errors
- Use a circuit breaker pattern to handle temporary outages