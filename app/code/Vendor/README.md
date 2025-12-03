# Vendor_Warranty

**Vendor_Warranty** is a Magento 2 module that allows customers to register product warranties and enables admins to manage registrations via the Magento Admin Panel and GraphQL API.

---

## Features

- Customers can register product warranties using a **serial number** and product SKU.
- Admin can manage warranty registrations in the backend.
- GraphQL endpoint for creating warranty registrations programmatically.
- Custom logging for warranty approvals (`var/log/warranty.log`).
- ACL support to restrict access to config and registrations grid.

---

## Installation

1. Copy the module to your Magento 2 instance:
	
	app/code/Vendor/Warranty

2. Enable the module and run setup commands:
	php bin/magento module:enable Vendor_Warranty
	php bin/magento setup:upgrade
	php bin/magento setup:di:compile
	php bin/magento cache:flush

## Configuration

	Navigate to: Stores → Settings → Configuration → Vendor Warranty → Warranty Settings

## Admin Menu

	Sales → Warranty Registrations
	View and manage all warranty registrations.
	ACL-protected: Vendor_Warranty::registrations.

	Assign access to admin roles under:
	System → Permissions → User Roles → Role Resources=

## GraphQL Usage:

	- registration_id:  ID of the registration
	- customer_id: ID of the customer
	- product_sku: SKU of the registered product
	- serial_number: Serial number provided
	- purchase_date: Date of product purchase
	- order_id:  Related order ID (if any)
	- proof_url: URL of purchase proof
	- status:  0 = Pending, 1 = Approved, 2 = Rejected
	- created_at: Record creation timestamp
	- updated_at: Record last update timestamp

## Simple query:

query {
	warrantyRegistration(id: 1) {
		registration_id
		customer_id
		product_sku
		serial_number
		purchase_date
		order_id
		proof_url
		status
		created_at
		updated_at
	}
}

## Filter by status, product_sku, serial_number

	query {
		warrantyRegistrations(
		filter: { serial_number: "number 1" },
			pageSize: 10,
			currentPage: 1,
			sort: { field: "created_at", direction: DESC }
			) {
			items {
				registration_id
				product_sku
				serial_number
				purchase_date
				order_id
				proof_url
				status
				created_at
				updated_at
			}
			total_count
			page_info {
			  page_size
			  current_page
			  total_pages
			}
		}
	}

## Craete new registrations mutation

	mutation CreateWarrantyRegistration {
	    createWarrantyRegistration(
	        input: {
	            product_sku: "24-MB02"
	            proof_url: "https://demo.com"
	            serial_number: "AAAAAAA"
	            purchase_date: "2025-10-09"
	        }
	    ) {
	        created_at
	        customer_id
	        order_id
	        product_sku
	        proof_url
	        purchase_date
	        registration_id
	        serial_number
	        status
	        updated_at
	    }
	}

## Update registrations mutation

	mutation UpdateWarrantyRegistration {
	    updateWarrantyRegistration(
	        input: { registration_id: 1, product_sku: "24-MB04" }
	    ) {
	        created_at
	        customer_id
	        order_id
	        product_sku
	        proof_url
	        purchase_date
	        registration_id
	        serial_number
	        status
	        updated_at
	    }
	}

### Create Token for register customer
	mutation GenerateCustomerToken {
	    generateCustomerToken(email: "test@gmail.com", password: "test@123") {
	        token
	    }
	}

##  Cron Job: Auto-Reject Old Pending Warranty Registrations

- This module includes an automated cron job that periodically cleans up old warranty registrations that have remained in the **pending** state for too long.

## Purpose

- Automatically mark **pending warranty registrations** as **rejected** if they are older than **90 days** from their creation date.

- This ensures the system remains clean and prevents long-stale pending requests from accumulating.

## Custom Logger

- var/log/warranty.log

## Events / Observers

- Event: vendor_warranty_approved
- Observer: WarrantyApprovedObserver

## Integration / Unit Tests

	vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/Vendor/Warranty/Test/Unit
	vendor/bin/phpunit -c dev/tests/integration/phpunit.xml.dist app/code/Vendor/Warranty/Test/Integration
