/**
 * Composable para validação e formatação de CPF
 */

export function useCpf() {
  /**
   * Valida um CPF brasileiro
   */
  function validarCpf(cpf: string): boolean {
    // Remove caracteres não numéricos
    const cpfLimpo = cpf.replace(/\D/g, '')
    
    // Verifica se tem 11 dígitos
    if (cpfLimpo.length !== 11) return false
    
    // Verifica se todos os dígitos são iguais (ex: 111.111.111-11)
    if (/^(\d)\1{10}$/.test(cpfLimpo)) return false
    
    // Valida os dígitos verificadores
    let soma = 0
    let resto
    
    // Valida 1º dígito
    for (let i = 1; i <= 9; i++) {
      soma += parseInt(cpfLimpo.substring(i - 1, i)) * (11 - i)
    }
    resto = (soma * 10) % 11
    if (resto === 10 || resto === 11) resto = 0
    if (resto !== parseInt(cpfLimpo.substring(9, 10))) return false
    
    // Valida 2º dígito
    soma = 0
    for (let i = 1; i <= 10; i++) {
      soma += parseInt(cpfLimpo.substring(i - 1, i)) * (12 - i)
    }
    resto = (soma * 10) % 11
    if (resto === 10 || resto === 11) resto = 0
    if (resto !== parseInt(cpfLimpo.substring(10, 11))) return false
    
    return true
  }
  
  /**
   * Formata CPF para exibição (000.000.000-00)
   */
  function formatarCpf(cpf: string): string {
    const cpfLimpo = cpf.replace(/\D/g, '')
    if (cpfLimpo.length !== 11) return cpf
    return cpfLimpo.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
  }
  
  /**
   * Máscara para input (###.###.###-##)
   */
  const mascaraCpf = '###.###.###-##'
  
  return {
    validarCpf,
    formatarCpf,
    mascaraCpf,
  }
}
