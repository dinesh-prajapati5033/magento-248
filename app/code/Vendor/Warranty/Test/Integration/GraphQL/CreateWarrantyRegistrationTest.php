<?php
/**
 * @category  Vendor
 * @package   Vendor_Warranty
 * @author    Vendor
 * @copyright Copyright (c) 2025 Vendor
 */
declare(strict_types=1);

namespace Vendor\Warranty\Test\Integration\GraphQL;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Integration test for CreateWarrantyRegistration GraphQL resolver.
 *
 * This test verifies that a warranty registration can be successfully created
 * via the GraphQL mutation, and checks that the response contains correct data.
 */
class CreateWarrantyRegistrationTest extends GraphQlAbstract
{
    /**
     * Test creating a warranty registration via GraphQL mutation.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testCreateWarrantyRegistration(): void
    {
        // SKU of a product that must exist in DB (created by fixture)
        $sku = 'simple-product';

        // Serial number for the test registration
        $serial = 'TEST-SERIAL-001';

        // GraphQL mutation string to create a warranty registration
        $mutation = <<<GRAPHQL
          mutation {
            createWarrantyRegistration(input: {
              product_sku: "$sku",
              serial_number: "$serial"
            }) {
              registration_id
              product_sku
              serial_number
              status
            }
          }
        GRAPHQL;

        // Execute the mutation via Magento's GraphQL test framework
        $response = $this->graphQlMutation($mutation);

        // Assert the mutation response contains the main key
        $this->assertArrayHasKey(
            'createWarrantyRegistration',
            $response,
            'Mutation response missing createWarrantyRegistration key'
        );

        // Extract the registration data
        $data = $response['createWarrantyRegistration'];

        // Assertions to verify correctness of returned data
        $this->assertEquals($sku, $data['product_sku'], 'Product SKU does not match input');
        $this->assertEquals($serial, $data['serial_number'], 'Serial number does not match input');
        $this->assertEquals(0, $data['status'], 'Registration status should be 0 (pending)');
        $this->assertNotEmpty($data['registration_id'], 'Registration ID should not be empty');
    }
}
