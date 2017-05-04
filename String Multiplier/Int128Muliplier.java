import edu.princeton.cs.algs4.StdOut;

public class Int128Muliplier {
    // ans = a * b
    public Int128Muliplier() {     
    }
    public static void main(String[] args) {
        long startTime = System.nanoTime();
        long endTime;
        Int128Muliplier solver = new Int128Muliplier();
        String ans;
        ans = solver.calculation(args[0], args[1]);
        endTime = System.nanoTime();
        StdOut.printf("Mult Result is: %s\n", ans);
        StdOut.printf("It takes %d nsecs\n", endTime-startTime);
    }
    private String calculation(String str1, String str2) {
        // ans = a * b
        int[] a = new int[128];
        int[] b = new int[128];
        int[] ans = new int[256];
        long startTime = System.nanoTime();
        long endTime;
        a = getNum(str1);
        b = getNum(str2);
        endTime = System.nanoTime();
        StdOut.printf("It takes %d nsecs in parsing\n", endTime-startTime);
        ans = mul(a, b);
        endTime = System.nanoTime();
        StdOut.printf("It takes %d nsecs in calculating", endTime-startTime);
        
        return toString(ans);
    }
    private int[] mul(int[] a, int[] b) {
        if (a.length != b.length)
            throw new java.lang.IllegalArgumentException();
        
        if (a.length == 2) {
            int[] c = new int[a.length<<1];
            if (a[1] == 0 && a[0] == 0) {
                c[3] = 0;
                c[2] = 0;
                c[1] = 0;
                c[0] = 0;
                return c;
            }
            else if (b[1] == 0 && b[0] == 0) {
                c[3] = 0;
                c[2] = 0;
                c[1] = 0;
                c[0] = 0;
                return c;
            }
            else { // a = cd, b = ef
                int ce = a[0]*b[0];
                int df = a[1]*b[1];
                int q = (a[0]+a[1])*(b[0]+b[1]);
                int result = ce*100+10*(q-ce-df)+df;
                // StdOut.printf("ce = %d, df = %d, q = %d, result = %d\n",ce,df,q,result);
                
                c[0] = result/1000;
                c[1] = result/100 - 10*c[0];
                c[2] = result/10 - 100*c[0] - 10*c[1];
                c[3] = result - 1000*c[0] - 100*c[1] - 10*c[2];
                
                // StdOut.printf("%s x %s = %s\n",toString(a),toString(b),toString(c));
                return c;
            }
        }
        else {
            int[] halfA1 = new int[a.length>>1];
            int[] halfA2 = new int[a.length>>1];
            int[] halfB1 = new int[b.length>>1];
            int[] halfB2 = new int[b.length>>1];
            int[] halfC1 = new int[a.length];
            int[] halfC2 = new int[a.length];
            int[] halfC3 = new int[a.length];
            int[] halfC4 = new int[a.length];
            int[] c = new int[a.length<<1];
            
            int counter = 0;
            for (int i = 0; i < a.length; i++) {
                if (i < a.length/2) {
                    halfA1[i] = a[i];
                    halfB1[i] = b[i];
                }
                else {
                    halfA2[i-a.length/2] = a[i];
                    halfB2[i-a.length/2] = b[i];
                }
            }
            halfC1 = mul(halfA1, halfB1);
            halfC4 = mul(halfA2, halfB2);
            halfC2 = mul(halfA1, halfB2);
            halfC3 = mul(halfB1, halfA2);
            c = add(halfC1, halfC2, halfC3, halfC4);
            
            // if (!checkZero(c))
            //    StdOut.printf("%s x %s = %s\n",toString(a),toString(b),toString(c));
            return c;
        }       
    }
    private int[] add(int[] a, int[] b, int[] c, int[] d) {
        
        int[] addResult = new int[a.length<<1];
        
        for (int i = 0; i < addResult.length; i++) {
            if (i < a.length)
                addResult[i] += a[i];
            if (i > a.length/2-1 && i < a.length*3/2)
                addResult[i] = addResult[i] + b[i-a.length/2] + c[i-a.length/2];
            if (i > a.length-1)
                addResult[i] += d[i-a.length];
        }
        
        for (int i = addResult.length-1; i > 0; i--) {
            while (addResult[i]>9) {
                addResult[i] -= 10;
                addResult[i-1] += 1;
            }                
        }
        
        if (addResult[0] > 10)
            addResult[0] = addResult[0]-10*(addResult[0]/10);
        
        return addResult;
    }
    private boolean checkZero(int[] a) {
        for (int i = 0; i < a.length; i++)
            if (a[i] != 0)
                return false;
        return true;
    }
    private static String toString(int[] a) {
        String str=""; 
        Boolean prefix = true; 
        for (int i = 0; i < a.length; i++) 
            if (prefix && a[i] == 0 && i!=a.length-1) 
                continue; 
            else { 
                str += Integer.toString(a[i]); 
                prefix = false; 
            } 
        return str;
    }
    private int[] getNum(String str) {
        if (str.length() > 128)
            throw new java.lang.IllegalArgumentException();
        
        int[] a = new int[128];
        
        for (int i=0; i < str.length(); i++)
            a[128-str.length()+i] = Integer.parseInt(str.substring(i, i+1));
        
        return a;
    }
}