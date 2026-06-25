export const IK_BASE = 'https://ik.imagekit.io/vijys5g3r'

export function ikSrcset(path: string, widths: number[]): string {
  const cleanPath = path.replace(/^\//, '')
  return widths.map((w) => `${IK_BASE}/tr:w-${w}/${cleanPath} ${w}w`).join(', ')
}
