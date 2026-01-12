<?php

class FormulaParser {
    private $precedence = [
        '=' => 1, '<' => 1, '>' => 1, '<=' => 1, '>=' => 1, '!=' => 1,
        '+' => 2, '-' => 2,
        '*' => 3, '/' => 3,
    ];
    private $functions = [
        'MAX' => -1, 'MIN' => -1, 'MOYENNE' => -1, // -1 for variadic
        'SI' => 3,
    ];

    /**
     * Évalue une formule de manière sécurisée
     * @param string $formula Ex: "(DS1 + DS2 + Examen*2) / 4"
     * @param array $values Ex: ['DS1' => 14, 'DS2' => 12, 'Examen' => 16]
     * @return float|null
     * @throws Exception
     */
    public function evaluer($formula, $values) {
        // 1. Validation de la formule
        if (!$this->validerFormule($formula)) {
            throw new Exception("Formule invalide ou contient des caractères non autorisés.");
        }
        
        // 2. Remplacer les variables par leurs valeurs (ou 'NULL' pour les absents)
        $expression = $this->substituerVariables($formula, $values);
        
        // 3. Convertir en notation polonaise inverse (RPN)
        $rpn = $this->toRPN($expression);
        
        // 4. Évaluer la RPN
        return $this->evaluateRPN($rpn);
    }

    /**
     * Valide qu'une formule ne contient que des éléments autorisés et a des parenthèses équilibrées.
     * @param string $formula
     * @return bool
     */
    private function validerFormule($formula) {
        // Whitelist stricte des caractères autorisés et fonctions/opérateurs connus
        // Permet: lettres, chiffres, underscores, opérateurs, parenthèses, virgules, espaces
        $allowed_chars_pattern = '/^[A-Za-z0-9_+\-*\/().,=!<>\s]+$/';
        
        if (!preg_match($allowed_chars_pattern, $formula)) {
            return false;
        }
        
        // Vérifier les parenthèses équilibrées
        $balance = 0;
        foreach (str_split($formula) as $char) {
            if ($char === '(') $balance++;
            if ($char === ')') $balance--;
            if ($balance < 0) return false;
        }
        
        return $balance === 0;
    }
    
    /**
     * Remplace les noms de variables dans la formule par leurs valeurs numériques ou 'NULL'.
     * @param string $formula
     * @param array $values
     * @return string
     */
    private function substituerVariables($formula, $values) {
        $substituted_formula = $formula;
        foreach ($values as $nom => $valeur) {
            $replacement = 'NULL';
            // Convert comma decimal to dot decimal for numeric check and calculation
            if (is_string($valeur) && strpos($valeur, ',') !== false) {
                $valeur = str_replace(',', '.', $valeur);
            }

            if (is_numeric($valeur)) {
                $replacement = (string)$valeur;
            } elseif (is_string($valeur) && in_array(strtoupper($valeur), ['ABS', 'DIS', 'DEF'])) {
                $replacement = 'NULL'; // Traiter les statuts spéciaux comme NULL
            }

            // Utiliser preg_replace pour un remplacement basé sur des mots complets
            $substituted_formula = preg_replace('/\b' . preg_quote($nom) . '\b/', $replacement, $substituted_formula);
        }
        return $substituted_formula;
    }

    /**
     * Convertit une expression infixée en notation polonaise inverse (RPN).
     * @param string $expression L'expression où les variables sont déjà substituées.
     * @return array RPN tokens
     * @throws Exception
     */
    private function toRPN($expression) {
        $output = [];
        $operators = [];
        $arg_counts = []; // Stack to keep track of argument counts for nested functions

        $regex = '/(NULL|\d+(\.\d+)?|[A-Z_a-z][A-Z_a-z0-9]*|<=|>=|!=|=|<|>|[+\-*\/(),])/i';
        preg_match_all($regex, $expression, $tokens);
        $tokens = array_filter($tokens[0], fn($t) => trim($t) !== '');

        foreach ($tokens as $token) {
            $token_upper = strtoupper($token);

            if (is_numeric($token) || $token_upper === 'NULL') {
                $output[] = $token_upper;
            } elseif (array_key_exists($token_upper, $this->functions)) {
                $operators[] = ['type' => 'function', 'value' => $token_upper];
                $arg_counts[] = 1; // A function call starts, assume at least one argument
            } elseif ($token === ',') {
                while (!empty($operators) && end($operators)['value'] !== '(') {
                    $output[] = array_pop($operators);
                }
                if (empty($operators)) {
                    throw new Exception("Mismatched parentheses or misplaced comma.");
                }
                if (!empty($arg_counts)) {
                    $arg_counts[count($arg_counts) - 1]++; // Increment argument count for the current function
                }
            } elseif (array_key_exists($token, $this->precedence)) {
                while (!empty($operators) && end($operators)['type'] === 'operator' && $this->precedence[end($operators)['value']] >= $this->precedence[$token]) {
                    $output[] = array_pop($operators);
                }
                $operators[] = ['type' => 'operator', 'value' => $token];
            } elseif ($token === '(') {
                error_log("FormulaParser: Processing '('. Token: $token, Operators: " . print_r($operators, true));
                // If the token before was a function, this is a function call
                if (!empty($operators) && end($operators)['type'] === 'function') {
                    $operators[] = ['type' => 'paren_func', 'value' => '('];
                } else {
                    $operators[] = ['type' => 'paren', 'value' => '('];
                }
            } elseif ($token === ')') {
                // Pop operators until a '(' is found
                while (!empty($operators) && !in_array(end($operators)['value'], ['(', 'paren_func'])) {
                    $output[] = array_pop($operators);
                }

                $paren = array_pop($operators); // Pop the '(' or 'paren_func'
                if ($paren === null) {
                    throw new Exception("Mismatched parentheses.");
                }

                // If the popped parenthesis was for a function, get the function and its arg count
                if ($paren['type'] === 'paren_func' && !empty($operators) && end($operators)['type'] === 'function') {
                    $func = array_pop($operators);
                    $count = array_pop($arg_counts);
                    $func['args'] = $count;
                    $output[] = $func;
                }
            } else {
                 throw new Exception("Unknown token: $token");
            }
        }

        while (!empty($operators)) {
            $op = array_pop($operators);
            if (in_array($op['type'], ['paren', 'paren_func'])) {
                throw new Exception("Mismatched parentheses.");
            }
            $output[] = $op;
        }

        return $output;
    }

    /**
     * Évalue une expression en notation polonaise inverse (RPN).
     * @param array $rpn RPN tokens
     * @return float|null
     * @throws Exception
     */
    private function evaluateRPN($rpn) {
        $stack = [];
        foreach ($rpn as $token) {
            if (is_numeric($token)) {
                $stack[] = (float)$token;
            } elseif ($token === 'NULL') {
                $stack[] = null;
            } elseif (is_array($token) && $token['type'] === 'operator') {
                if (count($stack) < 2) throw new Exception("Invalid syntax or insufficient operands for operator.");
                $b = array_pop($stack);
                $a = array_pop($stack);

                if ($a === null || $b === null) {
                    $stack[] = null; // Propagation de NULL pour les opérations arithmétiques
                    continue;
                }

                switch ($token['value']) {
                    case '+': $stack[] = $a + $b; break;
                    case '-': $stack[] = $a - $b; break;
                    case '*': $stack[] = $a * $b; break;
                    case '/': 
                        if ($b == 0) throw new Exception("Division by zero.");
                        $stack[] = $a / $b; 
                        break;
                    // Comparison operators return boolean (true/false)
                    case '=': $stack[] = (int)($a == $b); break;
                    case '<': $stack[] = (int)($a < $b); break;
                    case '>': $stack[] = (int)($a > $b); break;
                    case '<=': $stack[] = (int)($a <= $b); break;
                    case '>=': $stack[] = (int)($a >= $b); break;
                    case '!=': $stack[] = (int)($a != $b); break;
                    default: throw new Exception("Unknown operator: " . $token['value']);
                }
            } elseif (is_array($token) && $token['type'] === 'function') {
                $num_args = $token['args'];
                if (count($stack) < $num_args) {
                    throw new Exception("Invalid syntax or insufficient arguments for function {$token['value']}.");
                }
                
                $args = [];
                for ($i = 0; $i < $num_args; $i++) {
                    array_unshift($args, array_pop($stack));
                }

                switch ($token['value']) {
                    case 'MAX': 
                        $valid_args = array_filter($args, fn($v) => $v !== null);
                        $stack[] = empty($valid_args) ? null : max($valid_args); 
                        break;
                    case 'MIN': 
                        $valid_args = array_filter($args, fn($v) => $v !== null);
                        $stack[] = empty($valid_args) ? null : min($valid_args); 
                        break;
                    case 'MOYENNE':
                        $valid_args = array_filter($args, fn($v) => $v !== null);
                        $stack[] = empty($valid_args) ? null : array_sum($valid_args) / count($valid_args);
                        break;
                    case 'SI':
                        if ($num_args !== 3) throw new Exception("Function SI expects 3 arguments.");
                        $condition = array_shift($args); // Condition is first
                        $val_true = array_shift($args);
                        $val_false = array_shift($args);
                        $stack[] = ($condition > 0) ? $val_true : $val_false; // Treat non-zero as true
                        break;
                    default: throw new Exception("Unknown function: " . $token['value']);
                }
            }
        }
        if (count($stack) > 1) {
            throw new Exception("Invalid syntax: too many operands left on stack.");
        }
        $result = array_pop($stack);
        // Ensure result is float or null, not boolean from comparisons
        return is_bool($result) ? (float)(int)$result : $result;
    }
}