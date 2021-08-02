<?php

namespace Drupal\session_expire\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\user\UserData;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides settings for session_expire module.
 */
class SessionExpireSettingsForm extends ConfigFormBase {
  /**
   * The user.data service.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $dateFormatter;

  /**
   * Constructs an AutologoutSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param DateFormatterInterface $dateFormatter
   */
  public function __construct(ConfigFactoryInterface $config_factory, DateFormatterInterface $dateFormatter) {
    parent::__construct($config_factory);
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['session_expire.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'session_expire_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('session_expire.settings');
    $form['session_expire_desc'] = array(
      '#type'  => 'markup',
      '#value' => t('This module requires cron to be correctly configured and running for Drupal.'),
    );
    $interval_keys = array(0, 7200, 10800, 21600, 43200, 86400, 172800, 259200, 604800);
    $interval = array_map([$this->dateFormatter, 'formatInterval'], array_combine($interval_keys, $interval_keys));
    $interval['0'] = t('Everytime');
    $form['interval'] = array(
      '#type'          => 'select',
      '#title'         => t('Interval'),
      '#default_value' => $config->get('interval'),
      '#options'       => $interval,
      '#description'   => t('Run the cleanup at the specified interval. This tells Drupal how often to run the cleanup. On a busy site, you want that to be more frequent (e.g. every day at a minimum). You don\'t want it to be too frequent though (e.g. every hour), as it can tie up the sessions table for a long time. Cron must be configured to run more frequently than the value you chose here.')
    );

    $period_keys = array(1800, 3600, 7200, 10800, 21600, 28800, 36000, 43200, 86400, 172800, 259200, 604800, 1209600, 2419200);
    $period = array_map([$this->dateFormatter, 'formatInterval'], array_combine($period_keys, $period_keys));
    $period['1000000000'] = t('Never');

    $form['age'] = array(
      '#type'          => 'select',
      '#title'         => t('Age'),
      '#default_value' => $config->get('age'),
      '#options'       => $period,
      '#description'   => t('Expire sessions that are older than the specified age. Older entries will be discarded.')
    );

    $form['mode'] = array(
      '#type'          => 'radios',
      '#title'         => t('Session types'),
      '#default_value' => $config->get('mode'),
      '#options'       => array(
        t('Anonymous'),
        t('Both anonymous and authenticated users'),
      ),
      '#description'   => t('Types of sessions to discard. This option indicates whether only anonymous users, or both anonymous and authenticated users are expired. Note that if you choose authenticated users, they will be logged off and have to login again after the "age" specified above.'),
    );

    $form['log'] = [
      '#type' => 'checkbox',
      '#title' => 'Enable watchdog Automated Logout logging',
      '#default_value' => $config->get('log')
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $session_expire_settings = $this->config('session_expire.settings');

    $session_expire_settings->set('interval', $values['timeout'])
      ->set('age', $values['age'])
      ->set('mode', $values['mode'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
