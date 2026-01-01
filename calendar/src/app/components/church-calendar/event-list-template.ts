export interface EventListTemplateVars {
  timeHtml: string;
  titleHtml: string;
  lang?: string | null;
  types?: string[];
}

export function eventListTemplate(vars: EventListTemplateVars): string {
  const { timeHtml, titleHtml, lang, types } = vars;

  // small emoji fallback map
  const flagMap: Record<string, string> = { hu: '🇭🇺', en: '🇬🇧', de: '🇩🇪', sk: '🇸🇰', ro: '🇷🇴' };

  let flagHtml = '';
  if (lang) {
    const langLower = String(lang).toLowerCase();
    // prefer SVG asset when available
    const src = `/cal_images/flags/${langLower}.svg`;
    flagHtml = `<img class="type-icon" style="height:18px; margin-left:6px" title="${lang}" src="${src}" alt="${lang}" />`;
    // if no svg, use emoji fallback (handled by browser if file missing won't show, but we still offer emoji as fallback)
    if (!flagHtml) {
      const em = flagMap[langLower] || (String(lang).slice(0, 2).toUpperCase());
      flagHtml = `<span class="event-lang-flag" style="margin-right:6px; font-size:16px">${em}</span>`;
    }
  }

  let typesHtml = '';
  if (Array.isArray(types) && types.length > 0) {
    for (const t of types) {
      const tLower = String(t).toLowerCase();
      typesHtml += `<img class="type-icon" style="height:18px; margin-left:6px" title="${t}" src="/cal_images/types/${tLower}.png" alt="${t}" />`;
    }
  }

  return `
    <div class="fc-list-item-container" style="display:flex;align-items:center;gap:6px;">
      ${timeHtml}
      <div class="fc-list-item-main" style="display:flex;align-items:center;gap:6px;">
        ${titleHtml}
        ${flagHtml}
        ${typesHtml}
      </div>
    </div>
  `;
}
