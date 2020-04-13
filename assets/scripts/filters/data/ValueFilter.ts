import AbstractSingleFieldFilter from "./AbstractSingleFieldFilter";
import Artisan from "../../class/Artisan";

export default class ValueFilter<T> extends AbstractSingleFieldFilter<T> {
    public constructor(fieldName: string) {
        super(fieldName);
    }

    public matches(artisan: Artisan): boolean {
        if (!this.isActive() || this.matchesUnknown(artisan)) {
            return true;
        }

        let target: T = artisan[this.fieldName];

        for (let value of this.selectedValues.values()) {
            if (target === value) {
                return true;
            }
        }

        return false;
    }
}
