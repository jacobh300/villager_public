using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using Firebase;
using Firebase.Auth;

public class GetUserToken : MonoBehaviour
{
    private FirebaseAuth auth;

    // User login credentials
    public string email;
    public string password;

    void Start()
    {
        // Initialize Firebase Auth
        auth = FirebaseAuth.DefaultInstance;

        // Call the Login method to log in the user
        Login();
    }

    void Login()
    {
        auth.SignInWithEmailAndPasswordAsync(email, password).ContinueWith(task => {
            if (task.IsCanceled)
            {
                Debug.LogError("Login was canceled.");
                return;
            }

            if (task.IsFaulted)
            {
                Debug.LogError("Login encountered an error: " + task.Exception);
                return;
            }

            // Login successful, get the user's ID token
            FirebaseUser user = task.Result;
            user.TokenAsync(true).ContinueWith(tokenTask => {
                if (tokenTask.IsCanceled)
                {
                    Debug.LogError("Token retrieval was canceled.");
                    return;
                }

                if (tokenTask.IsFaulted)
                {
                    Debug.LogError("Token retrieval encountered an error: " + tokenTask.Exception);
                    return;
                }

                // Token retrieval successful, get the token
                string idToken = tokenTask.Result;

                // Use the token as needed
                Debug.Log("User ID token: " + idToken);
            });
        });
    }
}