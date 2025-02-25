export type IconfontId =
  | "favicon";

export type IconfontKey =
  | "Favicon";

export enum Iconfont {
  Favicon = "favicon",
}

export const ICONFONT_CODEPOINTS: { [key in Iconfont]: string } = {
  [Iconfont.Favicon]: "61697",
};
