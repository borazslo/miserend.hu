import cloneDeep from 'lodash/cloneDeep';

export class ScriptUtil {

  static clone<T>(orig: T): T {
    return cloneDeep(orig);
  }

  public static deepEqual(a: any, b: any): boolean {
    return JSON.stringify(a) === JSON.stringify(b);
  }

  public static isNotNull<T>(value: T | null | undefined): value is T {
    return value != null;
  }

  public static isNull(value: any): value is null | undefined {
    return value == null;
  }

}
