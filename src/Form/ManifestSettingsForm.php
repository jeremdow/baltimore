<?php

namespace Drupal\social_pwa\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Social PWA Manifest.
 */
class ManifestSettingsForm extends ConfigFormBase {

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The request context.
   *
   * @var \Drupal\Core\Routing\RequestContext
   */
  protected $requestContext;

  /**
   * Constructs a SiteInformationForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator.
   * @param \Drupal\Core\Routing\RequestContext $request_context
   *   The request context.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AliasManagerInterface $alias_manager, PathValidatorInterface $path_validator, RequestContext $request_context) {
    parent::__construct($config_factory);

    $this->aliasManager = $alias_manager;
    $this->pathValidator = $path_validator;
    $this->requestContext = $request_context;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('path.alias_manager'),
      $container->get('path.validator'),
      $container->get('router.request_context')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'social_pwa_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['social_pwa.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Get the default settings for the Social PWA Module
    $config = $this->config('social_pwa.settings');
    // Get the basic site settings
    $site_config = $this->config('system.site');

    // Development notice
    $form['notice'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Please notice:'),
      '#open' => FALSE,
      '#description' => $this->t('This module is still being developed. Therefore you might run into some issues or errors.</br>If you like this module, run into any issues or have any other feedback please inform me at frankgraave@getopensocial.com'),
    );

    // Start form
    $form['social_pwa_manifest_settings'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Configuration for Manifest.json'),
      '#open' => FALSE,
    );
    $form['social_pwa_manifest_settings']['shortname'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Shortname'),
      '#size' => 10,
      '#default_value' => $config->get('shortname'),
      '#required' => TRUE,
      '#description' => $this->t('This will be the name the "app" receives when it is added to the homescreen. So you might want to keep this short.'),
    );
    $form['social_pwa_manifest_settings']['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#size' => 40,
      '#default_value' => $config->get('name'),
      '#description' => $this->t('Put in the name of your site.'),
      '#field_suffix' => '<i>(For example, the value of your Basic Site Settings: </i>' . '<b>' . $site_config->get('name') . '</b>)',
    );
    // ---------------------------------------------------------------------------------
    // Sub-section for the Icon
    // ---------------------------------------------------------------------------------
    $form['social_pwa_manifest_settings']['icons'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Icons'),
    );
    $form['social_pwa_manifest_settings']['icons']['icon'] = array (
      '#type' => 'managed_file',
      '#title' => $this->t('Icon 256 x 256'),
      '#description' => $this->t('Provide an square (.png) image that serves as your icon. <i>(Minimum dimensions 256 x 256)</i>'),
      '#required' => TRUE,
      '#upload_location' => file_default_scheme() . '://images/touch/',
      '#upload_validators' => array(
        'file_validate_extensions' => array('png'),
        'file_validate_image_resolution' => array('256x256', '256x256'),
      ),
    );
    // ---------------------------------------------------------------------------------
    $front_page = $site_config->get('page.front') != '/user/login' ? $this->aliasManager->getAliasByPath($site_config->get('page.front')) : '';
    $form['social_pwa_manifest_settings']['start_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Start URL'),
      '#size' => 30,
      '#disabled' => TRUE,
      '#default_value' => $front_page,
      '#description' => $this->t('This is automatically set with the value from "Default Front Page" of the "Basic Site Settings".'),
      '#field_prefix' => $this->requestContext->getCompleteBaseUrl(),
    );
    $form['social_pwa_manifest_settings']['background_color'] = array(
      '#type' => 'color',
      '#title' => $this->t('Background Color'),
      '#default_value' => $config->get('background_color'),
      '#description' => $this->t('Select a background color for the launch screen.'),
    );
    $form['social_pwa_manifest_settings']['theme_color'] = array(
      '#type' => 'color',
      '#title' => $this->t('Theme Color'),
      '#default_value' => $config->get('theme_color'),
      '#description' => $this->t('Select a theme color.'),
    );
    $form['social_pwa_manifest_settings']['display'] = array(
      '#type' => 'select',
      '#title' => $this->t('Display'),
      '#default_value' => $config->get('display'),
      '#description' => $this->t('<u>When the site is being launched from the homescreen, you can launch it in:</u></br><b>Fullscreen:</b><i> This will cover up the entire display of the device.</i></br><b>Standalone:</b> <i>(default) Kind of the same as Fullscreen, but only shows the top info bar of the device. (Telecom provider, time, battery etc.)</i></br><b>Browser:</b> <i>It will simply just run from the browser on your device with all the user interface elements of the browser.</i>'),
      '#options' => array(
        'fullscreen' => $this->t('Fullscreen'),
        'standalone' => $this->t('Standalone'),
        'browser' => $this->t('Browser'),
      ),
    );
    $form['social_pwa_manifest_settings']['orientation'] = array(
      '#type' => 'select',
      '#title' => $this->t('Orientation'),
      '#default_value' => $config->get('orientation'),
      '#description' => $this->t('Configures if the site should run in <b>Portrait</b> (default) or <b>Landscape</b> mode on the device when being launched from the homescreen.'),
      '#options' => array(
        'portrait' => $this->t('Portrait'),
        'landscape' => $this->t('Landscape'),
      ),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate front page path.
    if (($value = $form_state->getValue('start_url')) && $value[0] !== '/') {
      $form_state->setErrorByName('start_url', $this->t("The path '%path' has to start with a slash.", ['%path' => $form_state->getValue('start_url')]));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('social_pwa.settings');
    $config->set('name', $form_state->getValue('name'))
      ->set('shortname', $form_state->getValue('shortname'))
      ->set('start_url', $form_state->getValue('start_url'))
      ->set('background_color', $form_state->getValue('background_color'))
      ->set('theme_color', $form_state->getValue('theme_color'))
      ->set('display', $form_state->getValue('display'))
      ->set('orientation', $form_state->getValue('orientation'))
      ->set('icons.icon', $form_state->getValue('icon'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
