

# Lexical analyser

Program takes code in IPP20 language from stdin and prints it's XML representation to stdout.
## Brief IPP20 language description
IPP20 is a three-address code with instruction name and it's operands.
Language has 3 memory frames: glodal, local and temporary.

**Basic types:**
int, bool, string, label, var (assign operations), type (language is dynamically typed).

Line of code can contain comments (start whith #).


**Instruction example**:  *ADD GF@a int@5 int@7* will save 12 to variable *a* on *GF* memory frame.
### Example:
``` 
~$ cat in.txt 
.IPPcode20
# WRITE will not print "\n"
WRITE int@0
WRITE string@ábč
```
```
~$ php parse.php < in.txt >out.xml
```

```
~$ cat out.txt<?xml version="1.0" encoding="UTF-8"?>

<program language="IPPcode20">
    <instruction order="1" opcode="WRITE">
        <arg1 type="int">0</arg1>
    </instruction>
    <instruction order="2" opcode="WRITE">
        <arg1 type="string">ábč</arg1>
    </instruction>
</program>
```