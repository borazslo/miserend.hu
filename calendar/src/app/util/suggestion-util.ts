import {Mass} from '../model/mass';
import {MassState, Suggestion} from '../model/suggestion';
import {ScriptUtil} from './script-util';
import {MassUtil} from './mass-util';
import {equals} from '@ngx-translate/core';

export class SuggestionUtil {

  public static generateSuggestions(
    masses: Map<number, Mass>,
    changes: Map<number, Mass>,
    deletedMasses: number[]
  ): Suggestion[] {

    const suggestions: Suggestion[] = [];

    // Kezeljük az új és módosított eseményeket
    changes.forEach((changedMass: Mass, id: number): void => {
      if (id < 0) {
        // Új esemény
        const suggestion: Suggestion = {
          ...(changedMass.periodId && {periodId: changedMass.periodId}),
          massState: MassState.NEW,
          changes: { ...changedMass },
        };
        if (suggestion.changes.id) {
          delete suggestion.changes.id;
        }
        suggestions.push(suggestion);
      } else {
        const original = masses.get(id);
        if (original) {
          const diff = SuggestionUtil.getMassChanges(original, changedMass);
          if (Object.keys(diff).length > 0) {
            suggestions.push({
              ...(changedMass.periodId && {periodId: changedMass.periodId}),
              massId: id,
              massState: MassState.MODIFIED,
              changes: diff,
            });
          }
        }
      }
    });

    // Kezeljük a törléseket
    for (const deletedId of deletedMasses) {
      suggestions.push({
        ...(masses.get(deletedId)?.periodId && {periodId: masses.get(deletedId)?.periodId}),
        massId: deletedId,
        massState: MassState.DELETED,
        changes: {},
      });
    }

    return suggestions;
  }

  public static generateInverseSuggestions(
    masses: Map<number, Mass>,
    suggestions: Suggestion[]
  ): {
    changes: Map<number, Mass>,
    changedMasses: number[],
    deletedMasses: number[],
    deletedDates: Map<number, string[]>
  } {
    const changes = new Map<number, Mass>();
    const deletedMasses: number[] = [];
    const changedMasses: number[] = [];
    const deletedDates = new Map<number, string[]>();

    for (const suggestion of suggestions) {
      switch (suggestion.massState) {
        case MassState.NEW: {
          const newMass = { ...suggestion.changes } as Mass;
          if (!newMass.id) {
            newMass.id = MassUtil.generateTmpMassId();
          }
          changes.set(newMass.id, newMass);
          break;
        }
        case MassState.MODIFIED: {
          if (suggestion.massId == null) continue;
          const originalMass = masses.get(suggestion.massId);
          if (!originalMass) continue;
          let dates:string[] =[];
          suggestion.changes['exdate']?.forEach((date:string) =>{
            if(!originalMass.exdate?.includes(date)){
              dates.push(date);
            }
          });
          // Merge-eljük az eredeti eseményt a változásokkal
          const updatedMass: Mass = {
            ...originalMass,
            ...suggestion.changes,
          };
          if(dates.length>0) {
            deletedDates.set(originalMass.id, dates);
            updatedMass.exdate = originalMass.exdate;
          }
          if(!equals(originalMass, updatedMass)) {
            changes.set(suggestion.massId, updatedMass);
          }
          changedMasses.push(originalMass.id);
          break;
        }
        case MassState.DELETED: {
          if (suggestion.massId != null) {
            deletedMasses.push(suggestion.massId);
          }
          break;
        }
      }
    }

    return { changes, changedMasses, deletedMasses, deletedDates };
  }


  private static getMassChanges(original: Mass, modified: Mass): any {
    const changes: any = {};
    for (const key in original) {
      const originalValue = (original as any)[key];
      const modifiedValue = (modified as any)[key];

      if (!ScriptUtil.deepEqual(originalValue, modifiedValue)) {
        changes[key] = modifiedValue;
      }
    }

    // Nézzük meg, ha a modified-ben van olyan kulcs, ami az original-ben nem
    for (const key in modified) {
      if (!(key in original)) {
        changes[key] = (modified as any)[key];
      }
    }

    return changes;
  }

  public static mergeMasses(baseMasses: Mass[], suggestions: Suggestion[]): Mass[] {
    const updatedMassesMap = new Map<number, Mass>();

    for (const mass of baseMasses) {
      updatedMassesMap.set(mass.id, ScriptUtil.clone(mass));
    }

    for (const suggestion of suggestions) {
      switch (suggestion.massState) {
        case MassState.NEW:
          const newMass = suggestion.changes as Mass;
          newMass.id = MassUtil.generateTmpMassId();
          updatedMassesMap.set(newMass.id, ScriptUtil.clone(newMass));
          break;

        case MassState.MODIFIED:
          const existingMass = updatedMassesMap.get(suggestion.massId!);
          if (existingMass) {
            const modifiedMass = {
              ...existingMass,
              ...suggestion.changes
            };
            updatedMassesMap.set(modifiedMass.id, modifiedMass);
          }
          break;

        case MassState.DELETED:
          updatedMassesMap.delete(suggestion.massId!);
          break;
      }
    }

    return Array.from(updatedMassesMap.values());
  }

  public static modifiedSuggestions(suggestions: Suggestion[], acceptedSuggestions: Suggestion[]): boolean {
    if (suggestions.length !== acceptedSuggestions.length) {
      return true;
    }

    for (let i=0;i < suggestions.length; i++) {
      if (!ScriptUtil.deepEqual(suggestions.at(i)?.changes, acceptedSuggestions.at(i)?.changes)) {
        return true;
      }
      if (suggestions.at(i)?.massState !== acceptedSuggestions.at(i)?.massState) {
        return true;
      }
    }

    return false;
  }
}
