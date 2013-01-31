/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package leer_php_crear_php;

import java.io.*;
import java.util.*;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 *
 * @author zeroones
 */
public class Leer_php_crear_php {
    
    String[] modelos = new String[101];
    String[] bejattos = new String[149];
    int c = 0, cb = 0;
    File php = null, archivo = null;
    FileReader fileread = null;    
    BufferedReader buferread = null;
    String enphp = "";
    /**
     * @param args the command line arguments
     */
    public void modelos()
    {
        Scanner leer = null;
        String linea = null;
        try {
            leer = new Scanner (new File("modelos.php"));
            leer.useDelimiter("\n");
            if(leer.hasNext())
            {
                while(leer.hasNext())
                {                
                    
                    linea = leer.next().trim();
                    linea = linea.substring(0, linea.length()-1);
                    //linea = linea.toLowerCase();
                    if(linea.contains(" "))
                    {
                        linea = linea.replace(" ", "%20");
                    }
                    modelos[c] = linea;
//                    System.out.println(modelos[c]);
                    c++;                    
                }
            }
            //System.out.println(c);
        } catch (FileNotFoundException ex) {
            System.out.println("no se pudo abrir modelos");
        }
        
    }
    public void bejattos()
    {
        Scanner leer = null;
        String linea = null;
        try {
            leer = new Scanner (new File("bejattos.php"));
            leer.useDelimiter("\n");
            if(leer.hasNext())
            {
                while(leer.hasNext())
                {                
                    linea = leer.next().trim();
                    linea = linea.toLowerCase();
                    bejattos[cb] = linea;
//                    System.out.println(bejattos[cb]);
                    cb++;                    
                }
            }
            //System.out.println(c);
        } catch (FileNotFoundException ex) {
            System.out.println("no se pudo abrir modelos");
        }
        
    }
    public void mandar()
    {
        String ph = null;
        for(int i=0;i<c;i++)
        {
            ph = modelos[i]+".php";
            if(ph.contains("%20"))
            {
                ph = ph.replace("%20", "");
            }
            crear_php("C:/xampp/htdocs/cva_optimizado-y-netbeans/leer_php_crear_php/cva/"+ph);
        }
    }
    public void crear_php(String a)
    {
        
        try {
            php = new File(a);
            php.createNewFile();
        } catch (IOException ex) {
            System.out.println("no se pudo crear el archivo "+a);
        }
    }
    
    public void leer_ph()
    {
        String linea = "";
        archivo = new File("arte.php");
        try {
            fileread = new FileReader (archivo);
            buferread = new BufferedReader(fileread);  
            try {
                while((linea = buferread.readLine())!=null)
                {
                   enphp = enphp + linea+"\n";
                }
                
            } catch (IOException ex) {
                System.out.println(ex);
            }
        } catch (FileNotFoundException ex) 
        {System.out.println("No se pudo abuferreadir el archivo" + ex);}   
    }
    
    public void formular_msj()
    {
        String ph = "";
        String cadena = ""; 
        String linea ="";
        String fuera="";
        String numero = "";
        boolean escribir = false;
        int bandera = 0;
        for(int i=0;i<c;i++)
        {
            
            for(int b=0;b<cb;b++)
            {
                linea = modelos[i].toLowerCase();
                if(linea.contains("%20"))
                {
                    linea = linea.replace("%20", "");
                }
                if(linea.equals(bejattos[b]))
                {
                    escribir = true;
                    ph = "C:/xampp/htdocs/cva_optimizado-y-netbeans/leer_php_crear_php/cva/"+modelos[i]+".php";
                    if(ph.contains("%20"))
                    {
                        ph = ph.replace("%20", "");
                    }
                    cadena = enphp.replace("3COM", modelos[i]);
                    if(cadena.contains("SELECT product_clave_cva FROM products WHERE product_clave_cva <> '' ORDER BY product_clave_cva ASC"))
                    {
                        numero = "SELECT product_clave_cva FROM products WHERE product_clave_cva <> '' AND product_brand = "+(b+1)+" ORDER BY product_clave_cva ASC";
                        cadena = cadena.replace("SELECT product_clave_cva FROM products WHERE product_clave_cva <> '' ORDER BY product_clave_cva ASC", numero);
                    }
                    if(cadena.contains("arte"))
                        cadena = cadena.replace("arte", ""+(b+1));
                    escribir_archivo(cadena, ph);
                    //System.out.println((b+1));
                    break;
                }
                else
                   escribir = false;
                
            }
//            System.out.println(escribir);
            if(escribir == false)
            {
                ph = "C:/xampp/htdocs/cva_optimizado-y-netbeans/leer_php_crear_php/cva/"+modelos[i]+".php";
                if(ph.contains("%20"))
                {
                    ph = ph.replace("%20", "");
                }
                cadena = enphp.replace("3COM", modelos[i]);
                escribir_archivo(cadena, ph);
//                System.out.println("1");
            }
        }
    }
    
    public void escribir_archivo(String msj, String direccion)
    {
        try {
            FileWriter archiv=new FileWriter(direccion, false);
            BufferedWriter file=new BufferedWriter(archiv);
            file.write(msj);
            file.close();
        } catch (IOException ex) {
        System.out.println("No se pudo escribir en el archivo "+ ex);
        }
    }
    
    public static void main(String[] args) {
        // TODO code application logic here
        Leer_php_crear_php p = new Leer_php_crear_php();
        p.modelos();
        p.bejattos();
        p.mandar();
        p.leer_ph();
        p.formular_msj();
    }
}
