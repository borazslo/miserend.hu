<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy;

class Jqplots
{
    public $title;
    public $labels = [];
    public $data = [];

    public function __construct($id)
    {
        $this->id = $id;

        $this->axes = [
            'xaxis' => [
                'label' => '',
                'renderer' => '$.jqplot.DateAxisRenderer',
                'tickOptions' => [
                    'formatString' => '%Y.%m.',
                ],
                'tickInterval' => '2 month',
            ],
            'yaxis' => [
                'tickOptions' => [
                    'formatString' => '%\'d',
                ],
                'rendererOptions' => [
                    'forceTickAt0' => true,
                ],
            ],
            'y2axis' => [
                'tickOptions' => [
                    'formatString' => "%'d",
                ],
                'rendererOptions' => [
                    'alignTicks' => true,
                    'forceTickAt0' => true,
                ],
            ],
        ];
    }

    public function prepare_script()
    {
        $return = "
			legend['labels'] = ".json_encode($this->labels).';
			plot_'.$this->id." = $.jqplot('".$this->id."', ".json_encode($this->data).", {
			// Turns on animatino for all series in this plot.
			animate: true,
			// Will animate plot on calls to plot1.replot({resetAxes:true})
			animateReplot: true,
			seriesDefaults: seriesDefaults,
			title: '".$this->title."',
			series:[
				{
					pointLabels: {
						show: true
					},
					renderer: $.jqplot.BarRenderer,
					showHighlight: false,
					yaxis: 'y2axis',
					rendererOptions: {
						// Speed up the animation a little bit.
						// This is a number of milliseconds.
						// Default for bar series is 3000.
						animation: {
							speed: 2500
						},
						barWidth: 15,
						barPadding: -15,
						barMargin: 0,
						highlightMouseOver: false
					}
				},
				{
					rendererOptions: {
						// speed up the animation a little bit.
						// This is a number of milliseconds.
						// Default for a line series is 2500.
						animation: {
							speed: 2000
						}
					}
				}
			],
			axesDefaults: {
				pad: 0
			},
			axes: {
".$this->array2jqueryscript($this->axes, 3).'
			} ,
			highlighter: highlighter,
			legend: legend,
			grid: grid,
		});
		';

        $this->script = $return;

        return true;
    }

    public function prepare_html()
    {
        $return = '<div id="'.$this->id.'" style="margin-top: 20px; margin-left: 20px; width: 100%; height: 300px; position: relative;" class="jqplot-target"></div>';

        $this->html = $return;

        return true;
    }

    public function array2jqueryscript($array, $level = 0)
    {
        $return = '';
        $c = 0;
        $len = \count($array);
        foreach ($array as $key => $value) {
            ++$c;
            for ($i = 0; $i <= $level; ++$i) {
                $return .= '    ';
            }
            $return .= $key.': ';
            if (\is_array($value)) {
                $return .= "{ \n";
                $return .= $this->array2jqueryscript($value, $level + 1);
                for ($i = 0; $i <= $level; ++$i) {
                    $return .= '    ';
                }
                $return .= '}';
            } else {
                if (true === $value) {
                    $return .= 'true';
                } else {
                    if (false === $value) {
                        $return .= 'false';
                    } else {
                        if (preg_match('/^\$\.jqplot/', $value) || is_numeric($value)) {
                            $return .= $value;
                        } else {
                            $return .= '"'.$value.'"';
                        }
                    }
                }
            }
            if ($c < $len) {
                $return .= ',';
            }

            $return .= "\n";
        }

        return $return;
    }
}
