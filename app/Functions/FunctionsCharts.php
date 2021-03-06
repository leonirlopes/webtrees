<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2018 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Fisharebest\Webtrees\Functions;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\FontAwesome;
use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Theme;

/**
 * Class FunctionsCharts - common functions
 */
class FunctionsCharts
{
    /**
     * print a table cell with sosa number
     *
     * @param string $daboville
     * @param string $pid  optional pid
     * @param string $icon which arrow to use
     *
     * @return void
     */
    public static function printDabovilleNumber(string $daboville, string $pid = '', string $icon = '')
    {
        // Remove trailing "."
        $personLabel = substr($daboville, 0, -1);

        if ($icon == '') {
            $visibility = 'hidden';
        } else {
            $visibility = 'normal';
        }
        echo '<td class="subheaders center" style="vertical-align: middle; text-indent: 0px; margin-top: 0px; white-space: nowrap; visibility: ', $visibility, ';">';
        echo $personLabel;
        if ($daboville != '1' && $pid !== '') {
            echo '<br>';
            echo FontAwesome::linkIcon($icon, $pid, ['href' => '#' . $pid]);
        }
        echo '</td>';
    }

    /**
     * print a table cell with sosa number
     *
     * @param int    $sosa
     * @param string $pid  optional pid
     * @param string $icon which arrow to use
     *
     * @return void
     */
    public static function printSosaNumber(int $sosa, string $pid = '', string $icon = '')
    {
        if ($icon == '') {
            $visibility = 'hidden';
        } else {
            $visibility = 'normal';
        }

        echo '<td class="subheaders center" style="vertical-align: middle; text-indent: 0px; margin-top: 0px; white-space: nowrap; visibility: ', $visibility, ';">';
        echo (string) $sosa;
        if ($sosa !== 1 && $pid !== '') {
            echo '<br>';
            echo FontAwesome::linkIcon($icon, $pid, ['href' => '#' . $pid]);
        }
        echo '</td>';
    }

    /**
     * print the parents table for a family
     *
     * @param Family $family    family gedcom ID
     * @param int    $sosa      child sosa number
     * @param string $daboville indi label (descendancy booklet)
     * @param string $parid     parent ID (descendancy booklet)
     * @param string $gparid    gd-parent ID (descendancy booklet)
     *
     * @return void
     */
    public static function printFamilyParents(Family $family, int $sosa = 0, string $daboville = '', string $parid = '', string $gparid = '')
    {
        $pbheight = Theme::theme()->parameter('chart-box-y') + 14;

        $husb = $family->getHusband();
        if ($husb) {
            echo '<a name="', $husb->xref(), '"></a>';
        } else {
            $husb = new Individual('M', "0 @M@ INDI\n1 SEX M", null, $family->tree());
        }
        $wife = $family->getWife();
        if ($wife) {
            echo '<a name="', $wife->xref(), '"></a>';
        } else {
            $wife = new Individual('F', "0 @F@ INDI\n1 SEX F", null, $family->tree());
        }

        if ($sosa) {
            echo '<p class="name_head">', $family->getFullName(), '</p>';
        }

        /**
         * husband side
         */
        echo '<table cellspacing="0" cellpadding="0" border="0"><tr><td rowspan="2">';
        echo '<table cellspacing="0" cellpadding="0" border="0"><tr>';

        if ($parid) {
            if ($husb->xref() == $parid) {
                self::printDabovilleNumber($daboville, '', 'blank');
            } else {
                self::printDabovilleNumber($daboville, '', '');
            }
        } elseif ($sosa) {
            self::printSosaNumber($sosa * 2, '', '');
        }
        if ($husb->isPendingAddition()) {
            echo '<td class="new">';
        } elseif ($husb->isPendingDeletion()) {
            echo '<td class="old">';
        } else {
            echo '<td>';
        }
        echo FunctionsPrint::printPedigreePerson($husb);
        echo '</td></tr></table>';
        echo '</td>';
        // husband’s parents
        $hfam = $husb->getPrimaryChildFamily();
        if ($hfam) {
            // remove the|| test for $sosa
            echo '<td rowspan="2"><img src="' . Theme::theme()->parameter('image-hline') . '"></td><td rowspan="2"><img  src="' . Theme::theme()->parameter('image-vline') . '" width="3" height="' . ($pbheight - 14) . '"></td>';
            echo '<td><img class="linea1 lined1"  src="' . Theme::theme()->parameter('image-hline') . '"></td><td>';
            // husband’s father
            if ($hfam && $hfam->getHusband()) {
                echo '<table cellspacing="0" cellpadding="0" border="0"><tr>';
                if ($sosa > 0) {
                    self::printSosaNumber($sosa * 4, $hfam->getHusband()->xref(), 'arrow-down');
                }
                if (!empty($gparid) && $hfam->getHusband()->xref() == $gparid) {
                    self::printDabovilleNumber(trim(substr($daboville, 0, -3), '.') . '.', '', 'arrow-up');
                }
                echo '<td>';
                echo FunctionsPrint::printPedigreePerson($hfam->getHusband());
                echo '</td></tr></table>';
            } elseif ($hfam && !$hfam->getHusband()) {
                // Empty box for grandfather
                echo '<table cellspacing="0" cellpadding="0" border="0"><tr>';
                echo '<td>';
                echo FunctionsPrint::printPedigreePerson($hfam->getHusband());
                echo '</td></tr></table>';
            }
            echo '</td>';
        }
        if ($hfam && ($sosa != -1)) {
            echo '<td rowspan="2">';
            echo FontAwesome::linkIcon('arrow-end', $hfam->getFullName(), ['href' => ($sosa == 0 ? $hfam->url() : '#' . $hfam->xref())]);
            echo '</td>';
        }
        if ($hfam) {
            // husband’s mother
            echo '</tr><tr><td><img class="linea2 lined2"  src="' . Theme::theme()->parameter('image-hline') . '"></td><td>';
            if ($hfam && $hfam->getWife()) {
                echo '<table cellspacing="0" cellpadding="0" border="0"><tr>';
                if ($sosa > 0) {
                    self::printSosaNumber($sosa * 4 + 1, $hfam->getWife()->xref(), 'arrow-down');
                }
                if (!empty($gparid) && $hfam->getWife()->xref() == $gparid) {
                    self::printDabovilleNumber(trim(substr($daboville, 0, -3), '.') . '.', '', 'arrow-up');
                }
                echo '<td>';
                echo FunctionsPrint::printPedigreePerson($hfam->getWife());
                echo '</td></tr></table>';
            } elseif ($hfam && !$hfam->getWife()) {
                // Empty box for grandmother
                echo '<table cellspacing="0" cellpadding="0" border="0"><tr>';
                echo '<td>';
                echo FunctionsPrint::printPedigreePerson($hfam->getWife());
                echo '</td></tr></table>';
            }
            echo '</td>';
        }
        echo '</tr></table>';
        echo '<br>';
        if ($sosa && $family->canShow()) {
            foreach ($family->facts(Gedcom::MARRIAGE_EVENTS) as $fact) {
                echo '<a href="', e($family->url()), '" class="details1">';
                echo $fact->summary();
                echo '</a>';
            }
        }
        echo '<br>';

        /**
         * wife side
         */
        echo '<table cellspacing="0" cellpadding="0" border="0"><tr><td rowspan="2">';
        echo '<table cellspacing="0" cellpadding="0" border="0"><tr>';
        if ($parid) {
            if ($wife->xref() == $parid) {
                self::printDabovilleNumber($daboville, '', 'blank');
            } else {
                self::printDabovilleNumber($daboville, '', '');
            }
        } elseif ($sosa) {
            self::printSosaNumber($sosa * 2 + 1, '', '');
        }
        if ($wife->isPendingAddition()) {
            echo '<td class="new">';
        } elseif ($wife->isPendingDeletion()) {
            echo '<td class="old">';
        } else {
            echo '<td>';
        }
        echo FunctionsPrint::printPedigreePerson($wife);
        echo '</td></tr></table>';
        echo '</td>';
        // wife’s parents
        $wfam = $wife->getPrimaryChildFamily();
        if ($wfam) {
            echo '<td rowspan="2"><img src="' . Theme::theme()->parameter('image-hline') . '"></td><td rowspan="2"><img src="' . Theme::theme()->parameter('image-vline') . '" width="3" height="' . ($pbheight - 14) . '"></td>';
            echo '<td><img class="linea3 lined3" src="' . Theme::theme()->parameter('image-hline') . '"></td><td>';
            // wife’s father
            if ($wfam && $wfam->getHusband()) {
                echo '<table cellspacing="0" cellpadding="0" border="0"><tr>';
                if ($sosa > 0) {
                    self::printSosaNumber($sosa * 4 + 2, $wfam->getHusband()->xref(), 'arrow-down');
                }
                if (!empty($gparid) && $wfam->getHusband()->xref() == $gparid) {
                    self::printDabovilleNumber(trim(substr($daboville, 0, -3), '.') . '.', '', 'arrow-up');
                }
                echo '<td>';
                echo FunctionsPrint::printPedigreePerson($wfam->getHusband());
                echo '</td></tr></table>';
            } elseif ($wfam && !$wfam->getHusband()) {
                // Empty box for grandfather
                echo '<table cellspacing="0" cellpadding="0" border="0"><tr>';
                echo '<td>';
                echo FunctionsPrint::printPedigreePerson($wfam->getHusband());
                echo '</td></tr></table>';
            }
            echo '</td>';
        }
        if ($wfam && ($sosa != -1)) {
            echo '<td rowspan="2">';
            echo FontAwesome::linkIcon('arrow-end', $wfam->getFullName(), ['href' => ($sosa == 0 ? $wfam->url() : '#' . $wfam->xref())]);

            echo '</td>';
        }
        if ($wfam) {
            // wife’s mother
            echo '</tr><tr><td><img class="linea4 lined4"  src="' . Theme::theme()->parameter('image-hline') . '"></td><td>';
            if ($wfam && $wfam->getWife()) {
                echo '<table cellspacing="0" cellpadding="0" border="0"><tr>';
                if ($sosa > 0) {
                    self::printSosaNumber($sosa * 4 + 3, $wfam->getWife()->xref(), 'arrow-down');
                }
                if (!empty($gparid) && $wfam->getWife()->xref() == $gparid) {
                    self::printDabovilleNumber(trim(substr($daboville, 0, -3), '.') . '.', '', 'arrow-up');
                }
                echo '<td>';
                echo FunctionsPrint::printPedigreePerson($wfam->getWife());
                echo '</td></tr></table>';
            } elseif ($wfam && !$wfam->getWife()) {
                // Empty box for grandmother
                echo '<table cellspacing="0" cellpadding="0" border="0"><tr>';
                echo '<td>';
                echo FunctionsPrint::printPedigreePerson($wfam->getWife());
                echo '</td></tr></table>';
            }
            echo '</td>';
        }
        echo '</tr></table>';
    }

    /**
     * print the children table for a family
     *
     * @param Family $family       family
     * @param string $childid      child ID
     * @param int    $sosa         child sosa number
     * @param string $label        indi label (descendancy booklet)
     * @param bool   $show_cousins display cousins on chart
     *
     * @return void
     */
    public static function printFamilyChildren(
        Family $family,
        string $childid = '',
        int $sosa = 0,
        string $label = '',
        bool $show_cousins = false
    ) {
        $bheight = Theme::theme()->parameter('chart-box-y');

        $pbheight = $bheight + 14;

        $children = $family->getChildren();
        $numchil  = count($children);

        echo '<table border="0" cellpadding="0" cellspacing="0"><tr>';
        if ($sosa > 0) {
            echo '<td></td>';
        }
        echo '<td><span class="subheaders">';
        if ($numchil === 0) {
            echo I18N::translate('No children');
        } else {
            echo I18N::plural('%s child', '%s children', $numchil, I18N::number($numchil));
        }
        echo '</span>';

        if ($sosa == 0 && Auth::isEditor($family->tree())) {
            echo '<br>';
            echo '<a href="' . e(route('add-child-to-family', [
                    'gender' => 'U',
                    'ged'    => $family->tree()->name(),
                    'xref'   => $family->xref(),
                ])) . '">' . I18N::translate('Add a child to this family') . '</a>';
            echo ' <a class="icon-sex_m_15x15" href="' . e(route('add-child-to-family', [
                    'gender' => 'M',
                    'ged'    => $family->tree()->name(),
                    'xref'   => $family->xref(),
                ])) . '" title="', I18N::translate('son'), '"></a>';
            echo ' <a class="icon-sex_f_15x15" href="' . e(route('add-child-to-family', [
                    'gender' => 'F',
                    'ged'    => $family->tree()->name(),
                    'xref'   => $family->xref(),
                ])) . '" title="', I18N::translate('daughter'), '"></a>';
            echo '<br><br>';
        }
        echo '</td>';
        if ($sosa > 0) {
            echo '<td></td><td></td>';
        }
        echo '</tr>';

        $nchi = 1;
        if ($children) {
            foreach ($children as $child) {
                echo '<tr>';
                if ($sosa != 0) {
                    if ($child->xref() == $childid) {
                        self::printSosaNumber($sosa, $childid, 'arrow-up');
                    } elseif (empty($label)) {
                        self::printDabovilleNumber('', '', 'arrow-up');
                    } else {
                        self::printDabovilleNumber($label . ($nchi++) . '.', '', 'arrow-up');
                    }
                }
                if ($child->isPendingAddition()) {
                    echo '<td class="new">';
                } elseif ($child->isPendingDeletion()) {
                    echo '<td class="old">';
                } else {
                    echo '<td>';
                }
                echo FunctionsPrint::printPedigreePerson($child);
                echo '</td>';
                if ($sosa != 0) {
                    // loop for all families where current child is a spouse
                    $famids = $child->getSpouseFamilies();
                    $maxfam = count($famids) - 1;
                    for ($f = 0; $f <= $maxfam; $f++) {
                        // multiple marriages
                        if ($f > 0) {
                            echo '</tr><tr><td></td>';
                            echo '<td style="text-align:end; vertical-align: top;">';
                            //find out how many cousins there are to establish vertical line on second families
                            $fchildren = $famids[$f]->getChildren();
                            $kids      = count($fchildren);

                            if ($show_cousins) {
                                if ($kids > 0) {
                                    echo '<img height="' . ((($kids * 80 / 2))) . 'px"';
                                } else {
                                    echo '<img height="' . ($pbheight - 14) / 2 . 'px"';
                                }
                            } else {
                                if ($f == $maxfam) {
                                    echo '<img height="' . ((($bheight / 2))) . 'px"';
                                } else {
                                    echo '<img height="' . $pbheight . 'px"';
                                }
                            }
                            echo ' width="3" src="' . Theme::theme()->parameter('image-vline') . '">';
                            echo '</td>';
                        }
                        echo '<td class="details1" style="text-align:center;">';
                        $spouse = $famids[$f]->getSpouse($child);

                        $marr = $famids[$f]->getFirstFact('MARR');
                        $div  = $famids[$f]->getFirstFact('DIV');
                        if ($marr) {
                            // marriage date
                            echo $marr->date()->minimumDate()->format('%Y');
                            // divorce date
                            if ($div) {
                                echo '–', $div->date()->minimumDate()->format('%Y');
                            }
                            echo '<img class="linea5 lined5 " width="100%" height="3" src="' . Theme::theme()->parameter('image-hline') . '">';
                        } else {
                            echo '<img width="100%" height="3" src="' . Theme::theme()->parameter('image-hline') . '">';
                        }
                        echo '</td>';
                        // spouse information
                        echo '<td style="vertical-align: center;">';
                        echo FunctionsPrint::printPedigreePerson($spouse);
                        echo '</td>';
                        // cousins
                        if ($show_cousins) {
                            self::printCousins($famids[$f]);
                        }
                    }
                }
                echo '</tr>';
            }
        } elseif ($sosa < 1) {
            // message 'no children' except for sosa
            if (preg_match('/\n1 NCHI (\d+)/', $family->gedcom(), $match) && $match[1] == 0) {
                echo '<tr><td><i class="icon-childless"></i> ' . I18N::translate('This family remained childless') . '</td></tr>';
            }
        }
        echo '</table><br>';
    }

    /**
     * print a family with Sosa-Stradonitz numbering system
     * ($rootid=1, father=2, mother=3 ...)
     *
     * @param Family $family       family gedcom
     * @param string $childid      tree root ID
     * @param int    $sosa         Sosa-Stradonitz number
     * @param string $daboville    d'Aboville number
     * @param string $parid        parent ID (descendancy booklet)
     * @param string $gparid       gd-parent ID (descendancy booklet)
     * @param bool   $show_cousins display cousins on chart
     *
     * @return void
     */
    public static function printSosaFamily(
        Family $family,
        string $childid,
        int    $sosa,
        string $daboville,
        string $parid,
        string $gparid,
        bool   $show_cousins
    ) {
        echo '<hr>';
        echo '<p class="family-break">';
        echo '<a name="', e($family->xref()), '"></a>';
        self::printFamilyParents($family, $sosa, $daboville, $parid, $gparid);
        echo '<br>';
        echo '<table cellspacing="0" cellpadding="0" border="0"><tr><td>';
        self::printFamilyChildren($family, $childid, $sosa, $daboville, $show_cousins);
        echo '</td></tr></table>';
        echo '<br>';
    }

    /**
     * builds and returns sosa relationship name in the active language
     *
     * @param int $sosa Sosa number
     *
     * @return string
     */
    public static function getSosaName(int $sosa): string
    {
        $path = '';

        while ($sosa > 1) {
            if ($sosa % 2 == 1) {
                $path = 'mot' . $path;
            } else {
                $path = 'fat' . $path;
            }
            $sosa = intdiv($sosa, 2);
        }

        return Functions::getRelationshipNameFromPath($path, null, null);
    }

    /**
     * print cousins list
     *
     * @param Family $family
     *
     * @return void
     */
    private static function printCousins(Family $family)
    {
        $bheight   = Theme::theme()->parameter('chart-box-y');
        $fchildren = $family->getChildren();
        $kids      = count($fchildren);

        echo '<td>';
        if ($kids) {
            echo '<table cellspacing="0" cellpadding="0" border="0" ><tr>';
            if ($kids > 1) {
                echo '<td rowspan="', $kids, '"><img width="3px" height="', (($bheight) * ($kids - 1)), 'px" src="', Theme::theme()->parameter('image-vline'), '"></td>';
            }
            $ctkids = count($fchildren);
            $i      = 1;
            foreach ($fchildren as $fchil) {
                if ($i == 1) {
                    echo '<td><img class="linea1" width="10px" height="3px" style="vertical-align:middle"';
                } else {
                    echo '<td><img class="linea1" width="10px" height="3px"';
                }
                if (I18N::direction() === 'ltr') {
                    echo ' style="padding-right: 2px;"';
                } else {
                    echo ' style="padding-left: 2px;"';
                }
                echo ' src="', Theme::theme()->parameter('image-hline'), '"></td><td>';
                echo FunctionsPrint::printPedigreePerson($fchil);
                echo '</td></tr>';
                if ($i < $ctkids) {
                    echo '<tr>';
                    $i++;
                }
            }
            echo '</table>';
        } else {
            // If there is known that there are no children (as opposed to no known children)
            if (preg_match('/\n1 NCHI (\d+)/', $family->gedcom(), $match) && $match[1] == 0) {
                echo ' <i class="icon-childless" title="', I18N::translate('This family remained childless'), '"></i>';
            }
        }
        echo '</td>';
    }
}
