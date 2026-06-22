const endpoint: string =
  import.meta.env.PUBLIC_IMAGEKIT_ENDPOINT ?? '';

export interface ImageKitOptions {
  width?: number;
  height?: number;
  quality?: number;
  format?: 'auto' | 'webp' | 'avif' | 'jpeg' | 'png';
  focus?: 'center' | 'top' | 'left' | 'bottom' | 'right';
  blur?: number;
}

function buildTransformations(opts: ImageKitOptions): string {
  const params: string[] = [];
  if (opts.width) params.push(`w-${opts.width}`);
  if (opts.height) params.push(`h-${opts.height}`);
  if (opts.quality) params.push(`q-${opts.quality}`);
  if (opts.format && opts.format !== 'auto') params.push(`f-${opts.format}`);
  if (opts.focus) params.push(`fo-${opts.focus}`);
  if (opts.blur) params.push(`bl-${opts.blur}`);
  return params.length > 0 ? `tr:${params.join(',')}` : '';
}

export function imageKitSrc(path: string, opts: ImageKitOptions = {}): string {
  if (!endpoint) return path;
  const base = endpoint.replace(/\/+$/, '');
  const cleanPath = path.replace(/^\/+/, '');
  const tr = buildTransformations(opts);
  if (tr) {
    return `${base}/${tr}/${cleanPath}`;
  }
  return `${base}/${cleanPath}`;
}
