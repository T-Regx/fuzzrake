<?php

namespace App\Utils;

class CommissionsStatusRegexps
{
    const HTML_CLEANER_REGEXPS = [
        '#</?(strong|b|i|span|center|a|em|font)[^>]*>#s' => '',
        '#(\s|&nbsp;|<br\s*/?>)+#s' => ' ',
        '#<style[^>]*>.*?</style>#s' => '',
        '# style="[^"]*"( (?=\>))?#s' => '',
        '#’|&\#39;#' => '\'',
    ];
    const COMMON_REPLACEMENTS = [
        'COMMISSIONS' => '(quotes? reviews|everything|(quotes and )?comm?iss?ions?((/| and | )quotes)?)',
        'OPEN' => '(open(?!ing)|(?!not? |aren\'t |are not? )(accepting|considering)|WE_CAN take)',
        'CLOSED' => '(closed?|(not?|aren\'t|are not?|no longer|don\'t) (TIMESPAN )?(accepting|seeking|taking( on)?|take( on)?)|can(\'| ?no)t open|on hold)',
        'fursuits' => 'fursuits?',
        '</div>' => ' ?</div> ?',
        '<div>' => ' ?<div( class="[^"]*")?> ?',
        '<p>' => ' ?<p( class="[^"]*")?> ?',
        '</p>' => ' ?</p> ?',
        'WE_CAN' => '(i|we) can(?! not? )',
        'WE_ARE' => '(we are|we\'re|i am|i\'?m|STUDIO_NAME (is|are))',
        'WE' => '(i|we)',
        'MONTHS' => '(january|jan|february|feb|march|mar|april|apr|may|may|june|jun|july|jul|august|aug|september|sep|sept|october|oct|november|nov|december|dec)',
        'TIMESPAN' => '(current(ly)?|(right )?now|at (this|the) time|for the time being|already|(now )?(always|permanently|indefinitely))',
    ];
    const FALSE_POSITIVES_REGEXES = [
        '(once|when) (WE_ARE STATUS for COMMISSIONS|COMMISSIONS are STATUS)',
        'will not be STATUS for COMMISSIONS',
        '(art|painted glass) COMMISSIONS: STATUS',
        'COMMISSIONS STATUS MONTHS',
    ];
    const GENERIC_REGEXES = [
        '((WE_ARE )?TIMESPAN|(TIMESPAN )?WE_ARE) \**STATUS\**( for)?( the| new| some| all| any more)?( fursuits)? (COMMISSIONS|projects|orders|quotes)( requests)?',
        'COMMISSIONS( status|:? are| have| info)?( TIMESPAN)?[-: ]+(&gt;)*STATUS(&lt;)*',
        'quotes? have TIMESPAN STATUS',
        'STATUS (for (new )?)?COMMISSIONS',
        '<div>TIMESPAN</div><div>STATUS</div><div>for COMMISSIONS</div>',
        '<p>COMMISSIONS are</p><p>STATUS</p>',
        '<p>status: STATUS</p>',
        '<div class="([^"]*[^a-z])?COMMISSIONS-STATUS"></div>',
        '<h2[^>]*>STATUS</h2>',
        'slots TIMESPAN STATUS',
        'WE_ARE (TIMESPAN|also) STATUS for COMMISSIONS',
        'WE_ARE STATUS for all costume, mascot and fursuit work TIMESPAN',
        'WE STATUS\.',
        'TIMESPAN COMMISSIONS status: fursuits STATUS',
        '\[ COMMISSIONS[. ]+STATUS \]',
    ];
}
