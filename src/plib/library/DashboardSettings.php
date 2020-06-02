<?php

namespace PleskExt\GrafanaIntegrationExample;

class DashboardSettings
{

    public const PANEL_TITLE_PREFIX = 'panel_title_prefix';
    public const DATA_MIN_VALUE = 'data_min_value';
    public const DATA_MAX_VALUE = 'data_max_value';
    public const DATA_START_VALUE = 'data_start_value';

    /**
     * @return array
     */
    public static function getDefaults(): array
    {
        return [
            self::PANEL_TITLE_PREFIX => 'Panel Example #',
            self::DATA_MIN_VALUE => -50,
            self::DATA_MAX_VALUE => 100,
            self::DATA_START_VALUE => null,
        ];
    }

    /**
     * @param string $fieldName
     *
     * @return string|null
     */
    public static function getValue(string $fieldName): ?string
    {
        $defaults = self::getDefaults();
        return array_key_exists($fieldName, $defaults)
            ? \pm_Settings::get($fieldName, $defaults[$fieldName])
            : null;
    }

    /**
     * @return array
     */
    public static function getAllValues(): array
    {
        $values = [];
        foreach (self::getDefaults() as $name => $defaultValue) {
            $values[$name] = \pm_Settings::get($name, $defaultValue);
        }
        return $values;
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     */
    public static function setAllValues(array $data)
    {
        $defaults = self::getDefaults();
        foreach ($data as $name => $value) {
            if (!array_key_exists($name, $defaults)) {
                continue;
            }
            \pm_Settings::set($name, $value);
        }
    }
}