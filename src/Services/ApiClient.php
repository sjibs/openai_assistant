<?php declare(strict_types = 1);

namespace Drupal\openai_assistant\Services;

use GuzzleHttp\ClientInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Service class for interacting with the OpenAI API.
 */
class ApiClient {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $httpClient;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Constructs an ApiClient object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(ClientInterface $http_client, ConfigFactoryInterface $config_factory) {
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
  }

  /**
   * Makes a request to the OpenAI API.
   *
   * @param string $endpoint
   *   The API endpoint.
   * @param array $options
   *   The request options.
   *
   * @return array
   *   The response data.
   */
  public function request(string $endpoint, array $options = []): array {
    $config = $this->configFactory->get('openai_assistant.settings');
    $apiKey = $config->get('openai_secret_key') ?: getenv('OPENAI_SECRET_KEY');

    $options['headers']['Authorization'] = 'Bearer ' . $apiKey;
    $options['headers']['Content-Type'] = 'application/json';

    $response = $this->httpClient->request($options['method'] ?? 'GET', $endpoint, $options);
    $data = json_decode($response->getBody()->getContents(), TRUE);

    return $data;
  }

  /**
   * Fetches the list of OpenAI models.
   *
   * @return array
   *   The list of models.
   */
  public function getModels(): array {
    $endpoint = 'https://api.openai.com/v1/models';
    $response = $this->request($endpoint);
    return $response['data'] ?? [];
  }

  /**
   * Fetches the list of OpenAI assistants.
   *
   * @return array
   *   The list of assistants.
   */
  public function getAssistants(): array {
    $endpoint = 'https://api.openai.com/v1/assistants';
    $options = [
      'headers' => [
        'OpenAI-Beta' => 'assistants=v2',
      ],
    ];
    $response = $this->request($endpoint, $options);
    return $response['data'] ?? [];
  }

  /**
   * Updates an assistant on the OpenAI backend.
   *
   * @param string $assistant_id
   *   The assistant ID.
   * @param array $data
   *   The data to update.
   *
   * @return array
   *   The response data.
   */
  public function updateAssistant(string $assistant_id, array $data): array {
    $endpoint = 'https://api.openai.com/v1/assistants/' . $assistant_id;
    $options = [
      'headers' => [
        'OpenAI-Beta' => 'assistants=v2',
      ],
      'method' => 'POST',
      'json' => $data,
    ];
    return $this->request($endpoint, $options);
  }

}