import {Checkbox} from "../class/Checkbox";
import {toggle} from "../jQueryUtils";
import {Radio} from "../class/Radio";
import {NO, YES} from "../consts";

let illBeCareful: Checkbox, ackProsAndCons: Checkbox, iLikeButtons: Checkbox;
let isAdult: Radio, wantsSfw: Radio;
let $prosConsContainer: JQuery<HTMLElement>, $ageContainer: JQuery<HTMLElement>,
    $wantsSfwContainer: JQuery<HTMLElement>, $dismissButton: JQuery<HTMLElement>;

function isReady(): boolean {
    return illBeCareful.isChecked() && ackProsAndCons.isChecked() && (isAdult.isVal(NO) || wantsSfw.isAnySelected());
}

function refreshAll(): void {
    toggle($prosConsContainer, illBeCareful.isChecked());
    toggle($ageContainer, illBeCareful.isChecked() && ackProsAndCons.isChecked());
    toggle($wantsSfwContainer, illBeCareful.isChecked() && ackProsAndCons.isChecked() && isAdult.isVal(YES));

    let emoticon: string, label: string;

    if (isReady()) {
        label = 'I will now click this button';
        emoticon = ' :)';
        $dismissButton.addClass('btn-primary');
        $dismissButton.removeClass('btn-secondary');
    } else {
        label = "I can't click this button yet";
        emoticon = ' :(';
        $dismissButton.removeClass('btn-primary');
        $dismissButton.addClass('btn-secondary');
    }

    $dismissButton.val(label + (iLikeButtons.isChecked() ? emoticon : ''));
}

function dismiss(): void {
    if (isReady()) {
        jQuery('#checklist-container, #checklist-done').toggle();
    }
}

export function init(): (() => void)[] {
    return [
        () => {
            $prosConsContainer = jQuery('#checklist-pros-and-cons-container');
            $ageContainer = jQuery('#checklist-age-container');
            $wantsSfwContainer = jQuery('#checklist-wants-sfw-container');
            $dismissButton = jQuery('#checklist-dismiss-btn');
            $dismissButton.on('click', dismiss);

            illBeCareful = new Checkbox('checklist-ill-be-careful', refreshAll);
            ackProsAndCons = new Checkbox('checklist-ack-pros-and-cons', refreshAll);
            iLikeButtons = new Checkbox('checklist-i-like-buttons', refreshAll);

            isAdult = new Radio('checklistIsAdult', refreshAll);
            wantsSfw = new Radio('checklistWantsSfw', refreshAll);
        },
        () => {
            refreshAll();
        },
    ];
}