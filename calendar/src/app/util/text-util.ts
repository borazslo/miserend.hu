import {Duration} from '../model/mass';

export class TextUtil {


  public static concatDays(days: string[], separator: string, lastSeparator: string ): string {
    if (days.length === 0) return '';
    if (days.length === 1) return days[0];
    if (days.length === 2) return `${days[0]}${lastSeparator}${days[1]}`;

    const allButLast = days.slice(0, -1).join(separator);
    const last = days[days.length - 1];
    return `${allButLast}${lastSeparator}${last}`;
  }

  public static getReadableDuration(duration: Duration): string {
    const parts: string[] = [];

    if (duration.days !== undefined) {
      parts.push(`${duration.days} nap`);
    }

    if (duration.hours !== undefined) {
      parts.push(`${duration.hours} óra`);
    }

    if (duration.minutes !== undefined) {
      parts.push(`${duration.minutes} perc`);
    }

    return parts.join(', ');
  }
}
