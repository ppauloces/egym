/**
 * Formata uma data ISO (YYYY-MM-DD) para o formato brasileiro (DD/MM/YYYY)
 * sem problemas de timezone
 */
export function formatarDataBR(data: string | null | undefined): string {
  if (!data) return '-'
  
  // Se a data já tem timezone ou hora, pega apenas a parte da data
  const dateStr = data.split('T')[0]
  const [year, month, day] = dateStr.split('-')
  
  return `${day}/${month}/${year}`
}

/**
 * Formata uma data para o input type="date" (YYYY-MM-DD)
 */
export function formatarDataParaInput(data: string | null | undefined): string {
  if (!data) return ''
  
  // Se já está no formato ISO correto, retorna
  if (data.match(/^\d{4}-\d{2}-\d{2}$/)) {
    return data
  }
  
  // Se tem timezone, remove
  return data.split('T')[0]
}

/**
 * Calcula dias de atraso entre uma data e hoje
 * Retorna: 
 * - Positivo: dias em atraso
 * - Zero: vence hoje
 * - Negativo: dias até o vencimento (futuro)
 */
export function calcularDiasAtraso(dataVencimento: string): number {
  const [year, month, day] = dataVencimento.split('T')[0].split('-')
  const vencimento = new Date(Number(year), Number(month) - 1, Number(day))
  const hoje = new Date()
  hoje.setHours(0, 0, 0, 0)
  
  const diff = Math.floor((hoje.getTime() - vencimento.getTime()) / (1000 * 60 * 60 * 24))
  return diff
}
