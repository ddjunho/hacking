
def reverse_rot(char):
    return chr((ord(char) - 13) & 0x7F)

def reverse_xor(char):
    return chr(ord(char) ^ 3)

# 하드코딩된 비교 문자열 (0x2018 주소에 있음)
encoded = "C@qpl==Bppl@<=pG<>@l>@Blsp<@l@AArqmGr=B@A>q@@B=GEsmC@ArBmAGlA=@q"

# 1. XOR 3 역변환
xor_reversed = ''.join(reverse_xor(c) for c in encoded)

# 2. 문자열 재배열 역변환
rearranged = xor_reversed[::-1]

# 3. ROT-13 역변환
flag = ''.join(reverse_rot(c) for c in rearranged)

print("플래그:", flag)
