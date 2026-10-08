// Shared environment flags.
export const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
export const isSP = () => window.matchMedia('(max-width: 768px)').matches;
export const finePointer = () => window.matchMedia('(hover: hover) and (pointer: fine)').matches;
export const saveData = !!(navigator.connection && navigator.connection.saveData);
export const EASE_OUT = 'cubic-bezier(.16,1,.3,1)';
export const EASE_IO = 'cubic-bezier(.65,0,.35,1)';
