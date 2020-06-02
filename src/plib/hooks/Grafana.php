<?php

use PleskExt\Grafana\Api\{
    GrafanaHookV110Interface,
    DatasourcesCollectionInterface,
    DashboardsCollectionInterface,
    NotificationChannelsCollectionInterface
};
use PleskExt\GrafanaIntegrationExample\DashboardSettings;

class Modules_GrafanaIntegrationExample_Grafana implements GrafanaHookV110Interface
{

    private const TD_DATA_SOURCE_ID = 'test-data-source';
    private const MYSQL_DATA_SOURCE_ID = 'public-rfam-data-source';
    private const DASHBOARD_ID = 'test-graph';

    /**
     * @param DatasourcesCollectionInterface $collection
     */
    public function appendDatasources(DatasourcesCollectionInterface $collection): void
    {
        /**
         * The data source provides randomly generated data
         */
        $collection->addCustomDatasource('testdata', self::TD_DATA_SOURCE_ID, []);

        /**
         * Public MySQL database
         */
        $collection->addMysqlDatasource(self::MYSQL_DATA_SOURCE_ID, 'Rfam', 'rfamro', '')
            ->setUrl('mysql-rfam-public.ebi.ac.uk:4497');
    }

    /**
     * @param DashboardsCollectionInterface $collection
     */
    public function appendDashboards(DashboardsCollectionInterface $collection): void
    {
        /**
         * https://grafana.com/docs/grafana/latest/reference/dashboard/
         */
        $dashboardTemplate = [
            'title' => 'Dashboard Example',
            'uid' => '111',  // not necessary as auto-filled by the Grafana extension
            'editable' => false,  // disables the ability to edit the dashboard in the view pane
            'schemaVersion' => 17,  // version of the JSON schema
            'panels' => [
                $this->getTestDataPanel(),
                $this->getMySQLPanel(),
            ],
            'graphTooltip' => 1,
            'time' => [
                'from' => 'now-5y',
                'to' => 'now',
            ],
            'timepicker' => [
                'refresh_intervals' => ['5s', '10s', '30s', '1m', '2m', '5m', '15m', '30m', '1h'],
            ],
            'refresh' => '2m',
        ];

        /**
         * ID is used to display the dashboard.
         * URI format: /modules/grafana/d/<module_id>/<dashboard_id>?theme=[light|dark]
         * see the file /src/plib/views/scripts/index/index.phtml
         */
        $collection->addDashboard(self::DASHBOARD_ID, $dashboardTemplate);
    }

    /**
     * @param int $id
     * @param string $title
     * @param string $dsName
     * @param array $targets
     * @param int $posY
     *
     * @return array
     */
    private function getPanelTemplate(
        int $id,
        string $title,
        string $dsName,
        array $targets,
        int $posY = 0): array
    {
        /**
         * https://grafana.com/docs/grafana/latest/reference/dashboard/#panels
         */
        return [
            /**
             * This ID is used to display the panel if it's necessary.
             * URI format: /modules/grafana/p/<module_id>/<dashboard_id>/<panel_id>?theme=[light|dark]&from=<interval_timestamp_sec>&to=<interval_timestamp_sec>
             * see the file /src/plib/views/scripts/index/index.phtml
             */
            'id' => $id,
            'title' => $title,
            'datasource' => $dsName,  // use a data source ID from the appendDatasources() method
            'aliasColors' => [],
            'bars' => false,
            'dashLength' => 10,
            'dashes' => false,
            'fill' => 1,
            'fillGradient' => 0,
            'gridPos' => [
                'h' => 8,
                'w' => 24,
                'x' => 0,
                'y' => 8 * $posY,
            ],
            'hiddenSeries' => false,
            'interval' => '5m',
            'legend' => [
                'avg' => false,
                'current' => false,
                'max' => false,
                'min' => false,
                'show' => true,
                'total' => false,
                'values' => false,
            ],
            'lines' => true,
            'linewidth' => 1,
            'maxDataPoints' => '',
            'nullPointMode' => 'null',
            'options' => [
                'dataLinks' => [],
            ],
            'percentage' => false,
            'pointradius' => 2,
            'points' => false,
            'renderer' => 'flot',
            'seriesOverrides' => [],
            'spaceLength' => 10,
            'stack' => false,
            'steppedLine' => false,
            'targets' => $targets,  // the targets format is mostly unique for different data sources
            'thresholds' => [],
            'timeFrom' => null,
            'timeRegions' => [],
            'timeShift' => null,
            'tooltip' => [
                'shared' => true,
                'sort' => 0,
                'value_type' => 'individual',
            ],
            'type' => 'graph',
            'xaxis' => [
                'buckets' => null,
                'mode' => 'time',
                'name' => null,
                'show' => true,
                'values' => [],
            ],
            'yaxes' => [
                [
                    'format' => 'short',
                    'label' => null,
                    'logBase' => 1,
                    'max' => null,
                    'min' => null,
                    'show' => true,
                ],
                [
                    'format' => 'short',
                    'label' => null,
                    'logBase' => 1,
                    'max' => null,
                    'min' => null,
                    'show' => true,
                ],
            ],
            'yaxis' => [
                'align' => false,
                'alignLevel' => null,
            ],
        ];
    }

    /**
     * @return array
     */
    private function getTestDataPanel(): array
    {
        $panelTitlePrefix = DashboardSettings::getValue(DashboardSettings::PANEL_TITLE_PREFIX);
        $dataMinValue = DashboardSettings::getValue(DashboardSettings::DATA_MIN_VALUE);
        $dataMaxValue = DashboardSettings::getValue(DashboardSettings::DATA_MAX_VALUE);
        $dataStartValue = DashboardSettings::getValue(DashboardSettings::DATA_START_VALUE);

        return $this->getPanelTemplate(
            1,
            $panelTitlePrefix . 'testdata',
            self::TD_DATA_SOURCE_ID,
            [
                [
                    'refId' => 'A',
                    'scenarioId' => 'random_walk',
                    'seriesCount' => 2,
                    'min' => trim($dataMinValue) !== ''
                        ? floatval($dataMinValue)
                        : null,
                    'max' => trim($dataMaxValue) !== ''
                        ? floatval($dataMaxValue)
                        : null,
                    'startValue' => trim($dataStartValue) !== ''
                        ? floatval($dataStartValue)
                        : null,
                    'spread' => 10,
                ],
            ],
            1
        );
    }

    /**
     * @return array
     */
    private function getMySQLPanel(): array
    {
        $panelTitlePrefix = DashboardSettings::getValue(DashboardSettings::PANEL_TITLE_PREFIX);

        return $this->getPanelTemplate(
            2,
            $panelTitlePrefix . 'mysql',
            self::MYSQL_DATA_SOURCE_ID,
            [
                [
                    'refId' => 'A',
                    'format' => 'time_series',
                    'metricColumn' => 'none',
                    'table' => 'family',
                    'select' => [
                        [
                            [
                                'params' => ['number_of_species'],
                                'type' => 'column',
                            ],
                        ],
                    ],
                    'timeColumn' => 'created',
                    'timeColumnType' => 'datetime',
                    'where' => [
                        [
                            'name' => '$__timeFilter',
                            'params' => [],
                            'type' => 'macro',
                        ],
                    ],
                ],
            ],
            2
        );
    }

    /**
     * @param NotificationChannelsCollectionInterface $collection
     */
    public function appendNotificationChannels(NotificationChannelsCollectionInterface $collection): void
    {
    }
}
